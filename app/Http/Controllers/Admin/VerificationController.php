<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\NotificationLog;
use App\Models\PaymentProof;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class VerificationController extends Controller
{
    public function index(Request $request)
    {
        return view('pages.verifications.index');
    }

    public function datatable(Request $request): JsonResponse
    {
        $recordsTotal = Invoice::where('status', 'menunggu_verifikasi')->count();

        $search = trim((string) $request->input('search.value', ''));

        $query = Invoice::query()
            ->with(['customer.package', 'paymentProof'])
            ->select('invoices.*')
            ->where('invoices.status', 'menunggu_verifikasi')
            ->orderBy('invoices.updated_at', 'desc');

        if ($search !== '') {
            $query->whereRaw(
                'LOWER(invoices.invoice_number) LIKE ? OR LOWER(customers.name) LIKE ?',
                ['%' . strtolower($search) . '%', '%' . strtolower($search) . '%']
            );
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
                'amount'         => $inv->amount,
                'formatted_amount'=> $inv->formattedAmount(),
                'due_date'       => $inv->due_date->format('d M Y'),
                'issued_at'      => $inv->issued_at->format('d M Y'),
                'submitted_at'   => $inv->updated_at->format('d M Y H:i'),
                'payment_proof'  => $inv->paymentProof ? [
                    'file_name'   => $inv->paymentProof->file_name,
                    'uploaded_at' => $inv->paymentProof->uploaded_at->format('d M Y H:i'),
                ] : null,
                'actions' => [
                    'show_url'   => route('admin.api.verifications.show', ['invoice' => $inv->id]),
                    'approve_url'=> route('admin.api.verifications.approve', ['invoice' => $inv->id]),
                    'reject_url' => route('admin.api.verifications.reject', ['invoice' => $inv->id]),
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

    public function show(Invoice $invoice): JsonResponse
    {
        $invoice->load(['customer.package', 'paymentProof']);
        return response()->json($invoice);
    }

    public function approve(Request $request, Invoice $invoice): JsonResponse
    {
        if ($invoice->status !== 'menunggu_verifikasi') {
            return response()->json(['message' => 'Tagihan tidak dalam status menunggu verifikasi.'], 422);
        }

        DB::beginTransaction();
        try {
            $invoice->update([
                'status'   => 'lunas',
                'paid_at'  => now(),
            ]);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal menyetujui tagihan.'], 500);
        }

        $this->sendNotification($invoice, 'confirmation');

        return response()->json(['message' => 'Tagihan berhasil disetujui dan ditandai lunas.']);
    }

    public function reject(Request $request, Invoice $invoice): JsonResponse
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ], [
            'rejection_reason.required' => 'Alasan penolakan wajib diisi.',
            'rejection_reason.max'      => 'Alasan penolakan maksimal 500 karakter.',
        ]);

        if ($invoice->status !== 'menunggu_verifikasi') {
            return response()->json(['message' => 'Tagihan tidak dalam status menunggu verifikasi.'], 422);
        }

        $invoice->update([
            'status'           => 'ditolak',
            'rejection_reason' => $request->rejection_reason,
        ]);

        $this->sendNotification($invoice, 'rejection');

        return response()->json(['message' => 'Tagihan berhasil ditolak.']);
    }

    private function sendNotification(Invoice $invoice, string $type): void
    {
        $customer = $invoice->customer;
        $user = $customer->user;
        $portalUrl = $customer->getPortalUrl();

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
                'notification_type'  => $type,
                'channel'           => $channel,
                'status'            => 'sent',
                'sent_at'           => now(),
                'meta'              => [
                    $channel === 'whatsapp' ? 'whatsapp_number' : 'email' => $recipient,
                    'invoice_number' => $invoice->invoice_number,
                    'amount'        => $invoice->formattedAmount(),
                    'billing_period'=> $invoice->billing_period->format('F Y'),
                    'due_date'      => $invoice->due_date->format('d M Y'),
                    'portal_url'    => $portalUrl,
                ],
            ]);
        }
    }
}
