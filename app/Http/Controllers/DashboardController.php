<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Package;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalCustomers = Customer::count();
        $activeCustomers = Customer::where('status', \App\Enums\CustomerStatus::AKTIF)->count();
        $activePercentage = $totalCustomers > 0 ? round(($activeCustomers / $totalCustomers) * 100) : 0;

        $now = Carbon::now();
        $paidThisMonth = Invoice::where('status', \App\Enums\InvoiceStatus::LUNAS)
            ->whereYear('paid_at', $now->year)
            ->whereMonth('paid_at', $now->month)
            ->sum('amount');
        $paidInvoicesThisMonth = Invoice::where('status', \App\Enums\InvoiceStatus::LUNAS)
            ->whereYear('paid_at', $now->year)
            ->whereMonth('paid_at', $now->month)
            ->count();

        $unpaidInvoices = Invoice::whereIn('status', [
            \App\Enums\InvoiceStatus::BELUM_BAYAR,
            \App\Enums\InvoiceStatus::MENUNGGU_VERIFIKASI,
        ])->count();
        $totalReceivables = Invoice::whereIn('status', [
            \App\Enums\InvoiceStatus::BELUM_BAYAR,
            \App\Enums\InvoiceStatus::MENUNGGU_VERIFIKASI,
        ])->sum('amount');

        $pendingVerifications = Invoice::where('status', \App\Enums\InvoiceStatus::MENUNGGU_VERIFIKASI)->count();

        $recentInvoices = Invoice::with('customer')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $packages = Package::withCount('activeCustomers')->get();
        $packageStats = $packages->map(fn ($pkg) => [
            'name' => $pkg->name,
            'speed' => $pkg->speed,
            'formatted_price' => $pkg->formattedPrice(),
            'customer_count' => $pkg->active_customers_count,
        ])->toArray();

        $stats = [
            'total_customers' => $totalCustomers,
            'active_customers' => $activeCustomers,
            'active_percentage' => $activePercentage,
            'revenue_this_month' => $paidThisMonth,
            'paid_invoices_this_month' => $paidInvoicesThisMonth,
            'total_receivables' => $totalReceivables,
            'unpaid_invoices' => $unpaidInvoices,
            'pending_verifications' => $pendingVerifications,
        ];

        return view('pages.dashboard.index', compact(
            'stats',
            'recentInvoices',
            'packageStats',
        ));
    }
}
