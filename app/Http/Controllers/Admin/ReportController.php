<?php

namespace App\Http\Controllers\Admin;

use App\Exports\CustomerReportExport;
use App\Exports\InvoiceReportExport;
use App\Exports\RevenueReportExport;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        return view('pages.reports.index');
    }

    public function serverSide(Request $request, string $type): JsonResponse
    {
        $draw     = (int) $request->input('draw', 1);
        $start    = (int) $request->input('start', 0);
        $length   = (int) $request->input('length', 15);
        $search   = $request->input('search.value', '');
        $orderCol = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'asc');
        $fromDate = $request->input('from_date');
        $toDate   = $request->input('to_date');

        return match ($type) {
            'invoices'  => $this->serverInvoices($draw, $start, $length, $search, $orderCol, $orderDir, $fromDate, $toDate),
            'customers' => $this->serverCustomers($draw, $start, $length, $search, $orderCol, $orderDir),
            'revenue'   => $this->serverRevenue($draw, $start, $length, $fromDate, $toDate),
            default     => response()->json(['message' => 'Jenis laporan tidak valid.'], 400),
        };
    }

    public function summary(Request $request, string $type): JsonResponse
    {
        $fromDate = $request->input('from_date');
        $toDate   = $request->input('to_date');

        return match ($type) {
            'invoices'  => response()->json(['summary' => $this->invoicesSummary($fromDate, $toDate)]),
            'customers' => response()->json(['summary' => $this->customersSummary()]),
            'revenue'   => response()->json(['summary' => $this->revenueSummary($fromDate, $toDate)]),
            default     => response()->json(['message' => 'Jenis laporan tidak valid.'], 400),
        };
    }

    public function export(Request $request, string $type)
    {
        $fromDate = $request->input('from_date');
        $toDate   = $request->input('to_date');

        $data = match ($type) {
            'revenue'   => $this->revenueData($fromDate, $toDate),
            'invoices'  => $this->invoicesData($fromDate, $toDate),
            'customers' => $this->customersData(),
            default     => [],
        };

        $export = match ($type) {
            'revenue'   => new RevenueReportExport($data),
            'invoices'  => new InvoiceReportExport($data),
            'customers' => new CustomerReportExport($data),
            default     => null,
        };

        if (!$export) {
            return response()->json(['message' => 'Jenis laporan tidak valid.'], 400);
        }

        return $export->download();
    }

    private function serverInvoices($draw, $start, $length, $search, $orderCol, $orderDir, $fromDate, $toDate): JsonResponse
    {
        $query = Invoice::with('customer.package');

        if ($fromDate) { $query->where('billing_period', '>=', $fromDate); }
        if ($toDate)   { $query->where('billing_period', '<=', $toDate); }

        $total = $query->count();

        // JS columns: 0=row#, 1=invoice_number, 2=customer_name, 3=package_name,
        //             4=billing_period, 5=amount, 6=due_date, 7=status, 8=paid_at
        $columns = [
            null, // 0: row number (non-orderable)
            'invoice_number',   // 1
            'customer_name',    // 2 (joined)
            'package_name',     // 3 (joined)
            'billing_period',   // 4
            'amount',           // 5
            'due_date',         // 6
            'status',           // 7
            'paid_at',          // 8
        ];
        $orderColName = $columns[$orderCol] ?? 'invoice_number';

        // Join for ordering by joined columns
        if ($orderCol === 2 || $orderCol === 3) {
            $query->leftJoin('customers', 'invoices.customer_id', '=', 'customers.id');
        }
        if ($orderCol === 3) {
            $query->leftJoin('packages', 'customers.package_id', '=', 'packages.id');
        }

        $query->orderBy($orderColName, $orderDir === 'desc' ? 'desc' : 'asc');

        $filtered = $query->count();
        $all = $query->skip($start)->take($length)->get();

        $data = $all->map(fn ($inv) => [
            'invoice_number'    => $inv->invoice_number,
            'customer_name'   => $inv->customer?->name,
            'package_name'    => $inv->customer?->package?->name,
            'billing_period' => $inv->billing_period->format('F Y'),
            'formatted_amount'=> $inv->formattedAmount(),
            'due_date'        => $inv->due_date->format('d M Y'),
            'status'          => $inv->status,
            'status_label'   => $inv->statusBadge()['label'],
            'paid_at'         => $inv->paid_at?->format('d M Y') ?? '',
        ]);

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $data->values(),
        ]);
    }

    private function serverCustomers($draw, $start, $length, $search, $orderCol, $orderDir): JsonResponse
    {
        $query = Customer::with(['package', 'invoices']);
        $total = $query->count();

        // JS columns: 0=row#, 1=code, 2=name, 3=package_name, 4=status,
        //             5=total_invoices, 6=total_revenue, 7=outstanding
        $columns = [
            null, // 0: row number
            'code',          // 1
            'name',          // 2
            'package_name',   // 3
            'status',        // 4
            'total_invoices',// 5
            'total_revenue', // 6
            'outstanding',    // 7
        ];
        $sortKey = $columns[$orderCol] ?? 'name';

        $all = $query->get()->map(fn ($cust) => [
            'code'           => $cust->code,
            'name'          => $cust->name,
            'package_name'  => $cust->package?->name,
            'status'        => $cust->status,
            'status_label'  => $cust->statusBadge()['label'],
            'total_invoices'=> $cust->invoices->count(),
            'paid_invoices' => $cust->invoices->where('status', 'lunas')->count(),
            'total_revenue' => $cust->invoices->where('status', 'lunas')->sum('amount'),
            'outstanding'   => $cust->invoices->whereNotIn('status', ['lunas'])->sum('amount'),
        ]);

        $filtered = $all->count();

        if ($search) {
            $all = $all->filter(fn ($c) =>
                stripos($c['name'], $search) !== false ||
                stripos($c['code'], $search) !== false ||
                stripos($c['package_name'], $search) !== false
            );
        }

        $all = $all->sortBy(fn ($item) => $item[$sortKey] ?? '', SORT_REGULAR, $orderDir === 'desc');
        $paginated = $all->slice($start, $length)->values();

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $paginated,
        ]);
    }

    private function serverRevenue($draw, $start, $length, $fromDate, $toDate): JsonResponse
    {
        $query = Invoice::query();

        if ($fromDate) { $query->where('billing_period', '>=', $fromDate); }
        if ($toDate)   { $query->where('billing_period', '<=', $toDate); }

        $total = Invoice::count();

        $monthly = $query
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
                'month_raw'         => $row->month,
                'total_count'       => (int) $row->total_count,
                'paid_count'        => (int) $row->paid_count,
                'paid_amount'       => (int) $row->paid_amount,
                'outstanding_amount' => (int) $row->outstanding_amount,
            ]);

        $filtered = $monthly->count();
        $paginated = $monthly->slice($start, $length)->values();

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $paginated,
        ]);
    }

    private function revenueSummary(?string $fromDate, ?string $toDate): array
    {
        $query = Invoice::query();
        if ($fromDate) { $query->where('billing_period', '>=', $fromDate); }
        if ($toDate)   { $query->where('billing_period', '<=', $toDate); }
        $all = $query->get();

        return [
            'total_count'       => $all->count(),
            'paid_count'       => $all->where('status', 'lunas')->count(),
            'unpaid_count'     => $all->where('status', 'belum_bayar')->count(),
            'total_revenue'     => $all->where('status', 'lunas')->sum('amount'),
            'total_outstanding' => $all->whereNotIn('status', ['lunas'])->sum('amount'),
            'collection_rate'  => $all->count() > 0 ? round(($all->where('status', 'lunas')->count() / $all->count()) * 100, 1) : 0,
        ];
    }

    private function invoicesSummary(?string $fromDate, ?string $toDate): array
    {
        $query = Invoice::query();
        if ($fromDate) { $query->where('billing_period', '>=', $fromDate); }
        if ($toDate)   { $query->where('billing_period', '<=', $toDate); }
        $all = $query->get();
        $overdue = $all->filter(fn ($inv) => $inv->isOverdue());

        return [
            'total_count'       => $all->count(),
            'paid_count'        => $all->where('status', 'lunas')->count(),
            'pending_count'     => $all->where('status', 'menunggu_verifikasi')->count(),
            'rejected_count'   => $all->where('status', 'ditolak')->count(),
            'unpaid_count'      => $all->where('status', 'belum_bayar')->count(),
            'total_revenue'     => $all->where('status', 'lunas')->sum('amount'),
            'total_outstanding' => $all->whereNotIn('status', ['lunas'])->sum('amount'),
            'overdue_count'     => $overdue->count(),
            'overdue_amount'    => $overdue->sum('amount'),
        ];
    }

    private function customersSummary(): array
    {
        $all = Customer::with(['package', 'invoices'])->get();

        return [
            'total_count'       => $all->count(),
            'active_count'      => $all->where('status', 'aktif')->count(),
            'isolir_count'      => $all->where('status', 'isolir')->count(),
            'nonaktif_count'    => $all->where('status', 'nonaktif')->count(),
            'total_revenue'     => $all->sum(fn ($c) => $c->invoices->where('status', 'lunas')->sum('amount')),
            'total_outstanding'  => $all->sum(fn ($c) => $c->invoices->whereNotIn('status', ['lunas'])->sum('amount')),
        ];
    }

    private function revenueData(?string $fromDate, ?string $toDate): array
    {
        $query = Invoice::query();
        if ($fromDate) { $query->where('billing_period', '>=', $fromDate); }
        if ($toDate)   { $query->where('billing_period', '<=', $toDate); }

        return $query
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
                'total_count'       => (int) $row->total_count,
                'paid_count'        => (int) $row->paid_count,
                'paid_amount'       => (int) $row->paid_amount,
                'outstanding_amount'=> (int) $row->outstanding_amount,
            ])->toArray();
    }

    private function invoicesData(?string $fromDate, ?string $toDate): array
    {
        $query = Invoice::with('customer.package');
        if ($fromDate) { $query->where('billing_period', '>=', $fromDate); }
        if ($toDate)   { $query->where('billing_period', '<=', $toDate); }

        return $query->get()->map(fn ($inv) => [
            'invoice_number'   => $inv->invoice_number,
            'customer_name'    => $inv->customer?->name,
            'package_name'    => $inv->customer?->package?->name,
            'billing_period'  => $inv->billing_period->format('F Y'),
            'formatted_amount'=> $inv->formattedAmount(),
            'due_date'        => $inv->due_date->format('d M Y'),
            'status_label'    => $inv->statusBadge()['label'],
            'paid_at'         => $inv->paid_at?->format('d M Y') ?? '',
        ])->toArray();
    }

    private function customersData(): array
    {
        return Customer::with(['package', 'invoices'])->get()->map(fn ($cust) => [
            'code'           => $cust->code,
            'name'          => $cust->name,
            'package_name'  => $cust->package?->name,
            'status_label'  => $cust->statusBadge()['label'],
            'total_invoices'=> $cust->invoices->count(),
            'paid_invoices' => $cust->invoices->where('status', 'lunas')->count(),
            'total_revenue' => $cust->invoices->where('status', 'lunas')->sum('amount'),
            'outstanding'   => $cust->invoices->whereNotIn('status', ['lunas'])->sum('amount'),
        ])->sortByDesc('total_revenue')->values()->toArray();
    }
}
