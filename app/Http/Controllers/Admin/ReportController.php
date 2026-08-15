<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\PaymentSubmission;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        return view('pages.reports.index');
    }

    public function generate(Request $request, string $type): JsonResponse
    {
        $fromDate = $request->input('from_date');
        $toDate   = $request->input('to_date');

        return match ($type) {
            'revenue'   => $this->revenue($fromDate, $toDate),
            'invoices'  => $this->invoices($fromDate, $toDate),
            'customers' => $this->customers($fromDate, $toDate),
            default     => response()->json(['message' => 'Jenis laporan tidak valid.'], 400),
        };
    }

    private function revenue(?string $fromDate, ?string $toDate): JsonResponse
    {
        $query = Invoice::query();

        if ($fromDate) {
            $query->where('billing_period', '>=', $fromDate);
        }
        if ($toDate) {
            $query->where('billing_period', '<=', $toDate);
        }

        $all = (clone $query)->get();

        $totalCount    = $all->count();
        $paidCount     = $all->where('status', 'lunas')->count();
        $pendingCount  = $all->where('status', 'menunggu_verifikasi')->count();
        $rejectedCount = $all->where('status', 'ditolak')->count();
        $unpaidCount   = $all->where('status', 'belum_bayar')->count();

        $totalRevenue    = $all->where('status', 'lunas')->sum('amount');
        $totalOutstanding = $all->whereNotIn('status', ['lunas'])->sum('amount');

        $collectionRate = $totalCount > 0
            ? round(($paidCount / $totalCount) * 100, 1)
            : 0;

        // Monthly breakdown
        $monthly = (clone $query)
            ->selectRaw("DATE_TRUNC('month', billing_period) as month")
            ->selectRaw('COUNT(*) as total_count')
            ->selectRaw("SUM(CASE WHEN status = 'lunas' THEN 1 ELSE 0 END) as paid_count")
            ->selectRaw("SUM(CASE WHEN status = 'lunas' THEN amount ELSE 0 END) as paid_amount")
            ->selectRaw("SUM(CASE WHEN status != 'lunas' THEN amount ELSE 0 END) as outstanding_amount")
            ->groupByRaw("DATE_TRUNC('month', billing_period)")
            ->orderByRaw("DATE_TRUNC('month', billing_period)")
            ->get()
            ->map(fn ($row) => [
                'month'             => Carbon::parse($row->month)->format('M Y'),
                'month_raw'        => $row->month,
                'total_count'      => (int) $row->total_count,
                'paid_count'       => (int) $row->paid_count,
                'paid_amount'      => (int) $row->paid_amount,
                'outstanding_amount' => (int) $row->outstanding_amount,
            ]);

        return response()->json([
            'summary' => [
                'total_count'      => $totalCount,
                'paid_count'      => $paidCount,
                'pending_count'    => $pendingCount,
                'rejected_count'   => $rejectedCount,
                'unpaid_count'     => $unpaidCount,
                'total_revenue'    => $totalRevenue,
                'total_outstanding'=> $totalOutstanding,
                'collection_rate'  => $collectionRate,
            ],
            'monthly' => $monthly,
        ]);
    }

    private function invoices(?string $fromDate, ?string $toDate): JsonResponse
    {
        $query = Invoice::with('customer.package');

        if ($fromDate) {
            $query->where('billing_period', '>=', $fromDate);
        }
        if ($toDate) {
            $query->where('billing_period', '<=', $toDate);
        }

        $all = (clone $query)->get();

        $totalCount    = $all->count();
        $paidCount     = $all->where('status', 'lunas')->count();
        $pendingCount  = $all->where('status', 'menunggu_verifikasi')->count();
        $rejectedCount = $all->where('status', 'ditolak')->count();
        $unpaidCount   = $all->where('status', 'belum_bayar')->count();

        $totalRevenue     = $all->where('status', 'lunas')->sum('amount');
        $totalOutstanding = $all->whereNotIn('status', ['lunas'])->sum('amount');
        $overdueInvoices = $all->filter(fn ($inv) => $inv->isOverdue());
        $overdueCount     = $overdueInvoices->count();
        $overdueAmount    = $overdueInvoices->sum('amount');

        $data = $all->map(fn ($inv) => [
            'invoice_number' => $inv->invoice_number,
            'customer_name'  => $inv->customer?->name,
            'package_name'  => $inv->customer?->package?->name,
            'billing_period' => $inv->billing_period->format('F Y'),
            'amount'        => $inv->amount,
            'formatted_amount' => $inv->formattedAmount(),
            'due_date'      => $inv->due_date->format('d M Y'),
            'status'        => $inv->status,
            'status_label'  => $inv->statusBadge()['label'],
            'is_overdue'    => $inv->isOverdue(),
            'paid_at'       => $inv->paid_at?->format('d M Y'),
        ]);

        return response()->json([
            'summary' => [
                'total_count'      => $totalCount,
                'paid_count'       => $paidCount,
                'pending_count'    => $pendingCount,
                'rejected_count'   => $rejectedCount,
                'unpaid_count'     => $unpaidCount,
                'total_revenue'    => $totalRevenue,
                'total_outstanding'=> $totalOutstanding,
                'overdue_count'    => $overdueCount,
                'overdue_amount'   => $overdueAmount,
            ],
            'data' => $data->values(),
        ]);
    }

    private function customers(?string $fromDate, ?string $toDate): JsonResponse
    {
        $query = Customer::with(['package', 'invoices']);

        $all = $query->get();

        $totalCount   = $all->count();
        $activeCount  = $all->where('status', 'aktif')->count();
        $isolirCount  = $all->where('status', 'isolir')->count();
        $nonaktifCount = $all->where('status', 'nonaktif')->count();

        $data = $all->map(fn ($cust) => [
            'code'             => $cust->code,
            'name'             => $cust->name,
            'package_name'     => $cust->package?->name,
            'package_price'   => $cust->package?->price,
            'status'           => $cust->status,
            'status_label'    => $cust->statusBadge()['label'],
            'total_invoices'  => $cust->invoices->count(),
            'paid_invoices'   => $cust->invoices->where('status', 'lunas')->count(),
            'total_revenue'   => $cust->invoices->where('status', 'lunas')->sum('amount'),
            'outstanding'     => $cust->invoices->whereNotIn('status', ['lunas'])->sum('amount'),
        ])->sortByDesc('total_revenue')->values();

        return response()->json([
            'summary' => [
                'total_count'    => $totalCount,
                'active_count'   => $activeCount,
                'isolir_count'   => $isolirCount,
                'nonaktif_count' => $nonaktifCount,
                'total_revenue'  => $all->sum(fn ($c) => $c->invoices->where('status', 'lunas')->sum('amount')),
                'total_outstanding' => $all->sum(fn ($c) => $c->invoices->whereNotIn('status', ['lunas'])->sum('amount')),
            ],
            'data' => $data,
        ]);
    }
}
