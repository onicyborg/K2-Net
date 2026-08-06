@extends('layouts.app')

@section('title', 'Dashboard — K2-Net')

@section('toolbar')
    <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 my-0">Dashboard</h1>
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Home</a>
        </li>
        <li class="breadcrumb-item">
            <span class="text-muted">/</span>
        </li>
        <li class="breadcrumb-item text-gray-900 fw-bold">Dashboard</li>
    </ul>
@endsection

@section('content')
{{-- Stat Cards --}}
<div class="row g-5 g-xl-10 mb-5 mb-xl-10">

    {{-- Total Pelanggan --}}
    <div class="col-sm-6 col-xl-3">
        <div class="card card-flush h-100">
            <div class="card-header pt-5 pb-3">
                <div class="card-title d-flex flex-column">
                    <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">
                        {{ number_format($stats['total_customers']) }}
                    </span>
                    <span class="text-gray-500 fw-semibold fs-6 mt-2">Total Pelanggan</span>
                </div>
            </div>
            <div class="card-footer pt-0 pb-5 d-flex justify-content-end">
                <a href="{{ route('admin.customers.index') }}" class="btn btn-sm btn-light-primary">Kelola Pelanggan</a>
            </div>
        </div>
    </div>

    {{-- Pendapatan Bulan Ini --}}
    <div class="col-sm-6 col-xl-3">
        <div class="card card-flush h-100" style="background: linear-gradient(135deg, #009ef7 0%, #2ceef0 100%)">
            <div class="card-header pt-5 pb-3" style="background: transparent; border-bottom: 0">
                <div class="card-title d-flex flex-column">
                    <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">
                        Rp{{ number_format($stats['revenue_this_month'], 0, ',', '.') }}
                    </span>
                    <span class="text-white fw-semibold fs-6 mt-2" style="opacity: 0.9">Pendapatan Bulan Ini</span>
                </div>
            </div>
            <div class="card-footer pt-0 pb-5 d-flex justify-content-end" style="background: transparent; border-top: 0">
                <span class="badge badge-light text-dark">{{ $stats['paid_invoices_this_month'] }} invoice lunas</span>
            </div>
        </div>
    </div>

    {{-- Total Piutang --}}
    <div class="col-sm-6 col-xl-3">
        <div class="card card-flush h-100" style="background: linear-gradient(135deg, #f1416c 0%, #f98d30 100%)">
            <div class="card-header pt-5 pb-3" style="background: transparent; border-bottom: 0">
                <div class="card-title d-flex flex-column">
                    <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">
                        Rp{{ number_format($stats['total_receivables'], 0, ',', '.') }}
                    </span>
                    <span class="text-white fw-semibold fs-6 mt-2" style="opacity: 0.9">Total Piutang</span>
                </div>
            </div>
            <div class="card-footer pt-0 pb-5 d-flex justify-content-end" style="background: transparent; border-top: 0">
                <span class="badge badge-light text-dark">{{ $stats['unpaid_invoices'] }} invoice belum lunas</span>
            </div>
        </div>
    </div>

    {{-- Menunggu Verifikasi --}}
    <div class="col-sm-6 col-xl-3">
        <div class="card card-flush h-100">
            <div class="card-header pt-5 pb-3">
                <div class="card-title d-flex flex-column">
                    <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">
                        {{ number_format($stats['pending_verifications']) }}
                    </span>
                    <span class="text-gray-500 fw-semibold fs-6 mt-2">Menunggu Verifikasi</span>
                </div>
            </div>
            <div class="card-footer pt-0 pb-5 d-flex justify-content-end">
                <a href="{{ route('admin.verifications.index') }}" class="btn btn-sm btn-light-primary">Verifikasi</a>
            </div>
        </div>
    </div>

</div>

{{-- Invoice Terbaru + Paket Populer --}}
<div class="row g-5 g-xl-10">

    {{-- Invoice Terbaru --}}
    <div class="col-lg-8 mb-5 mb-xl-0">
        <div class="card card-flush h-100">
            <div class="card-header pt-5">
                <div class="card-title d-flex flex-column">
                    <span class="card-label fw-bold text-gray-900 fs-4">Invoice Terbaru</span>
                </div>
                <div class="card-toolbar">
                    <a href="{{ route('admin.invoices.index') }}" class="btn btn-sm btn-light-primary">Lihat Semua</a>
                </div>
            </div>
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed gs-0 gy-3">
                        <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th class="min-w-150px">Pelanggan</th>
                                <th class="min-w-100px">Periode</th>
                                <th class="min-w-100px text-end">Nominal</th>
                                <th class="min-w-100px">Status</th>
                                <th class="min-w-100px">Jatuh Tempo</th>
                            </tr>
                        </thead>
                        <tbody class="fw-semibold text-gray-700">
                            @forelse($recentInvoices as $invoice)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.invoices.show', $invoice->id) }}"
                                       class="text-gray-900 fw-bold text-hover-primary">
                                        {{ $invoice->customer->name ?? '-' }}
                                    </a>
                                </td>
                                <td class="text-muted">{{ $invoice->billing_period->format('M Y') }}</td>
                                <td class="text-end text-muted">{{ $invoice->formattedAmount() }}</td>
                                <td>
                                    <span class="badge badge-light-{{ $invoice->statusBadge()['class'] }}">
                                        {{ $invoice->statusBadge()['label'] }}
                                    </span>
                                </td>
                                <td class="text-muted">
                                    @if($invoice->isOverdue())
                                        <span class="text-danger fw-bold">{{ $invoice->due_date->format('d M Y') }}</span>
                                    @else
                                        {{ $invoice->due_date->format('d M Y') }}
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-10">
                                    <i class="ki-duotone ki-information-5 fs-3x mb-3">
                                        <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                    </i>
                                    <p class="fs-6 mb-0">Belum ada invoice tercatat.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Paket Populer --}}
    <div class="col-lg-4 mb-5 mb-xl-0">
        <div class="card card-flush h-100">
            <div class="card-header pt-5">
                <div class="card-title d-flex flex-column">
                    <span class="card-label fw-bold text-gray-900 fs-4">Paket Internet</span>
                </div>
            </div>
            <div class="card-body pt-0">
                @forelse($packageStats as $stat)
                <div class="d-flex flex-stack py-4 border-bottom border-gray-300 border-bottom-dashed">
                    <div class="d-flex align-items-center">
                        <div class="mb-0 me-2">
                            <span class="fs-6 text-gray-800 fw-bold">{{ $stat['name'] }}</span>
                            <div class="text-gray-500 fs-7">{{ $stat['speed'] }} — {{ $stat['formatted_price'] }}/bulan</div>
                        </div>
                    </div>
                    <span class="badge badge-light-primary fs-7">{{ $stat['customer_count'] }} pelanggan</span>
                </div>
                @empty
                <div class="text-center text-muted py-10">
                    <i class="ki-duotone ki-information-5 fs-3x mb-3">
                        <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                    </i>
                    <p class="fs-6 mb-0">Belum ada paket internet.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection
