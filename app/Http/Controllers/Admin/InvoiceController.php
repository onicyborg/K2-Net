<?php

namespace App\Http\Controllers\Admin;

use App\Events\InvoiceCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\NotificationLog;
use App\Models\Package;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        return view('pages.invoices.index');
    }

    public function datatable(Request $request): JsonResponse
    {
        $recordsTotal = Invoice::count();

        $search = trim((string) $request->input('search.value', ''));

        $query = Invoice::query()
            ->with('customer.package')
            ->select('invoices.*')
            ->orderBy('invoices.created_at', 'desc');

        if ($search !== '') {
            $query->whereRaw(
                'LOWER(invoices.invoice_number) LIKE ? OR LOWER(customers.name) LIKE ?',
                ['%' . strtolower($search) . '%', '%' . strtolower($search) . '%']
            );
        }

        $status = trim((string) $request->input('status', ''));
        if ($status !== '') {
            $query->where('invoices.status', $status);
        }

        $recordsFiltered = $query->count();

        $start  = max(0, (int) $request->input('start', 0));
        $length = min(100, max(1, (int) $request->input('length', 10)));

        $rows = $query->skip($start)->take($length)->get();

        $data = $rows->map(function (Invoice $inv) {
            return [
                'id'              => $inv->id,
                'invoice_number'  => $inv->invoice_number,
                'customer_name'   => $inv->customer?->name,
                'customer_id'     => $inv->customer_id,
                'billing_period'  => Carbon::parse($inv->billing_period)->format('M Y'),
                'amount'          => $inv->amount,
                'formatted_amount'=> $inv->formattedAmount(),
                'due_date'        => $inv->due_date->format('d M Y'),
                'status'          => $inv->status,
                'status_badge'    => $inv->statusBadge(),
                'is_overdue'      => $inv->isOverdue(),
                'paid_at'         => $inv->paid_at?->format('d M Y'),
                'issued_at'       => $inv->issued_at->format('d M Y'),
                'actions'         => [
                    'show_url'    => route('admin.api.invoices.show', ['invoice' => $inv->id]),
                    'edit_url'    => route('admin.api.invoices.update', ['invoice' => $inv->id]),
                    'delete_url'  => route('admin.api.invoices.destroy', ['invoice' => $inv->id]),
                ],
            ];
        });

        return response()->json([
            'draw'            => (int) $request->input('draw', 1),
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }

    public function generate(GenerateInvoiceRequest $request): JsonResponse
    {
        $customer = Customer::with('package', 'user')->findOrFail($request->customer_id);

        $startDate = Carbon::parse($request->billing_period_start);
        $endDate   = Carbon::parse($request->billing_period_end);

        $dueDay = \App\Models\SystemConfiguration::getValue('invoice_due_day', 15);

        $months = $startDate->copy();
        $createdInvoices = [];

        DB::beginTransaction();
        try {
            while ($months->lte($endDate)) {
                $period      = $months->copy()->startOfMonth();
                $dueDate     = $period->copy()->addMonth()->day($dueDay)->startOfDay();

                $existing = Invoice::where('customer_id', $customer->id)
                    ->where('billing_period', $period->toDateString())
                    ->first();

                if ($existing) {
                    $months->addMonth();
                    continue;
                }

                $invoiceNumber = $this->generateInvoiceNumber($customer, $period);

                $invoice = Invoice::create([
                    'invoice_number'  => $invoiceNumber,
                    'customer_id'    => $customer->id,
                    'billing_period' => $period->toDateString(),
                    'amount'         => $customer->package->price,
                    'due_date'       => $dueDate->toDateString(),
                    'status'         => 'belum_bayar',
                    'issued_at'      => now(),
                ]);

                // ============================================================
                // Trigger event InvoiceCreated → Listener akan otomatis kirim
                // WhatsApp via WhatsAppService (Kondisi 1 dari requirement).
                // ============================================================
                event(new InvoiceCreated($invoice));

                $createdInvoices[] = $invoice;
                $months->addMonth();
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal membuat tagihan: ' . $e->getMessage()], 500);
        }

        $emailResult = $this->sendBatchNotification($createdInvoices, $customer);

        $count = count($createdInvoices);

        if (($emailResult['status'] ?? null) === 'failed') {
            return response()->json([
                'message'  => "$count tagihan berhasil dibuat. ⚠️ Email gagal dikirim.",
                'invoices' => $createdInvoices,
                'email_failure' => $emailResult,
            ], 201);
        }

        return response()->json([
            'message'  => "$count tagihan berhasil dibuat dan email terkirim.",
            'invoices' => $createdInvoices,
        ], 201);
    }

    public function show(Invoice $invoice): JsonResponse
    {
        $invoice->load('customer.package');
        return response()->json($invoice);
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice): JsonResponse
    {
        $invoice->update($request->validated());
        return response()->json(['message' => 'Tagihan berhasil diperbarui.']);
    }

    public function destroy(Invoice $invoice): JsonResponse
    {
        if ($invoice->status === 'lunas') {
            return response()->json(['message' => 'Tagihan lunas tidak dapat dihapus.'], 422);
        }
        $invoice->delete();
        return response()->json(['message' => 'Tagihan berhasil dihapus.']);
    }

    private function generateInvoiceNumber(Customer $customer, Carbon $period): string
    {
        $prefix = 'INV';
        $month  = $period->format('m');
        $year   = $period->format('Y');
        $count  = Invoice::whereYear('billing_period', $year)
            ->whereMonth('billing_period', $month)
            ->count() + 1;
        return sprintf('%s/%s/%s/%04d', $prefix, $month, $year, $count);
    }

    private function sendBatchNotification(array $invoices, Customer $customer): array
    {
        $user = $customer->user;
        $portalUrl = $customer->getPortalUrl();

        // Log notification for each invoice
        foreach ($invoices as $invoice) {
            foreach (['whatsapp', 'email'] as $channel) {
                $recipient = $channel === 'whatsapp'
                    ? ($customer->whatsapp_number_full ?? $customer->whatsapp_number)
                    : $user?->email;

                if (!$recipient) {
                    continue;
                }

                NotificationLog::create([
                    'invoice_id'         => $invoice->id,
                    'customer_id'        => $customer->id,
                    'notification_type'  => 'reminder_h3',
                    'channel'           => $channel,
                    'status'            => 'sent',
                    'sent_at'           => now(),
                    'meta'              => [
                        $channel === 'whatsapp' ? 'whatsapp_number' : 'email' => $recipient,
                        'invoice_number'  => $invoice->invoice_number,
                        'amount'         => $invoice->formattedAmount(),
                        'billing_period' => $invoice->billing_period->format('F Y'),
                        'due_date'       => $invoice->due_date->format('d M Y'),
                        'portal_url'     => $portalUrl,
                        'batch_count'    => count($invoices),
                    ],
                ]);
            }
        }

        if (!$user?->email) {
            return ['status' => 'skipped', 'reason' => 'no_email'];
        }

        // Build invoice rows for email
        $totalAmount = 0;
        $invoiceRows = '';
        foreach ($invoices as $invoice) {
            $totalAmount += $invoice->amount;
            $invoiceRows .= '<tr>' .
                '<td style="padding:8px;border:1px solid #ddd;">' . $invoice->invoice_number . '</td>' .
                '<td style="padding:8px;border:1px solid #ddd;">' . $invoice->billing_period->format('F Y') . '</td>' .
                '<td style="padding:8px;border:1px solid #ddd;text-align:right;">Rp ' . number_format($invoice->amount, 0, ',', '.') . '</td>' .
                '<td style="padding:8px;border:1px solid #ddd;">' . $invoice->due_date->format('d M Y') . '</td>' .
                '</tr>';
        }

        $generatedMonth = now()->format('F Y');
        $count = count($invoices);
        $subject = $count > 1
            ? "Tagihan {$count} Bulan — {$generatedMonth}"
            : "Tagihan {$invoices[0]->invoice_number} — {$generatedMonth}";

        try {
            Mail::send([], [], function ($message) use ($user, $invoiceRows, $totalAmount, $portalUrl, $generatedMonth, $count, $subject) {
                $message->to($user->email, $user->name)
                    ->subject($subject)
                    ->html("
                        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                            <div style='background: #0091ea; color: white; padding: 20px; text-align: center;'>
                                <h2 style='margin:0;'>K2-Net</h2>
                                <p style='margin:5px 0 0;'>Tagihan Internet</p>
                            </div>
                            <div style='padding: 20px; background: #f9f9f9;'>
                                <p>Halo <strong>{$user->name}</strong>,</p>
                                <p>Berikut {$count} tagihan Anda:</p>
                                <table style='width: 100%; border-collapse: collapse; margin: 15px 0;'>
                                    <thead>
                                        <tr style='background:#e5e5e5;'>
                                            <th style='padding:8px;border:1px solid #ddd;text-align:left;'>No. Tagihan</th>
                                            <th style='padding:8px;border:1px solid #ddd;text-align:left;'>Periode</th>
                                            <th style='padding:8px;border:1px solid #ddd;text-align:right;'>Jumlah</th>
                                            <th style='padding:8px;border:1px solid #ddd;text-align:left;'>Jatuh Tempo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {$invoiceRows}
                                    </tbody>
                                    <tfoot>
                                        <tr style='background:#dbeafe;font-weight:bold;'>
                                            <td colspan='2' style='padding:8px;border:1px solid #ddd;'>TOTAL</td>
                                            <td style='padding:8px;border:1px solid #ddd;text-align:right;'>Rp " . number_format($totalAmount, 0, ',', '.') . "</td>
                                            <td style='padding:8px;border:1px solid #ddd;'></td>
                                        </tr>
                                    </tfoot>
                                </table>
                                <p style='text-align: center; margin: 20px 0;'>
                                    <a href='{$portalUrl}' style='background: #0091ea; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;'>Bayar Sekarang</a>
                                </p>
                                <p style='color: #666; font-size: 12px; text-align: center;'>
                                    Atau salin tautan berikut ke browser:<br/>
                                    <a href='{$portalUrl}' style='color: #0091ea;'>{$portalUrl}</a>
                                </p>
                            </div>
                            <div style='padding: 15px; text-align: center; color: #999; font-size: 12px; border-top: 1px solid #eee;'>
                                K2-Net — Sistem Manajemen Tagihan & Pelanggan
                            </div>
                        </div>
                    ");
            });
            return ['status' => 'success', 'email' => $user->email, 'count' => $count];
        } catch (\Throwable $e) {
            Log::error('[InvoiceNotification] Gagal kirim email batch tagihan', [
                'customer_id'     => $customer->id,
                'recipient_email'  => $user->email,
                'invoice_count'   => $count,
                'error'           => $e->getMessage(),
            ]);
            return ['status' => 'failed', 'email' => $user->email, 'error' => $e->getMessage()];
        }
    }
}
