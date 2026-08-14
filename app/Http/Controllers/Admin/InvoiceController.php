<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Package;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        $customer = Customer::with('package')->findOrFail($request->customer_id);

        $startDate = Carbon::parse($request->billing_period_start);
        $endDate   = Carbon::parse($request->billing_period_end);

        $months = $startDate->copy();
        $createdInvoices = [];

        DB::beginTransaction();
        try {
            while ($months->lte($endDate)) {
                $period      = $months->copy()->startOfMonth();
                $dueDate     = $period->copy()->addDays(14);

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

                $createdInvoices[] = $invoice;
                $months->addMonth();
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal membuat tagihan: ' . $e->getMessage()], 500);
        }

        $count = count($createdInvoices);
        return response()->json([
            'message'  => "$count tagihan berhasil dibuat untuk {$customer->name}.",
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
}
