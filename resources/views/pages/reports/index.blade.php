@extends('layouts.app')

@section('title', 'Pelaporan — K2-Net')

@section('toolbar')
    <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 my-0">Pelaporan</h1>
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Home</a>
        </li>
        <li class="breadcrumb-item"><span class="text-muted">/</span></li>
        <li class="breadcrumb-item text-gray-900 fw-bold">Pelaporan</li>
    </ul>
@endsection

@section('content')

{{-- Filter Card --}}
<div class="card card-flush mb-5">
    <div class="card-header pt-5 pb-0">
        <h3 class="card-title">Filter Laporan</h3>
    </div>
    <div class="card-body py-5">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold text-gray-700">Jenis Laporan</label>
                <select id="report_type" class="form-select form-select-solid">
                    <option value="invoices">Tagihan</option>
                    <option value="revenue">Pendapatan</option>
                    <option value="customers">Pelanggan</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold text-gray-700">Dari Tanggal</label>
                <input type="date" id="from_date" class="form-control form-control-solid" />
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold text-gray-700">Sampai Tanggal</label>
                <input type="date" id="to_date" class="form-control form-control-solid" />
            </div>
            <div class="col-md-3">
                <button type="button" id="btn_generate" class="btn btn-primary w-100">
                    <i class="ki-duotone ki-magnifier fs-2 me-1"><span></span></i>
                    Tampilkan
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Results Card --}}
<div class="card card-flush">
    <div class="card-header pt-5 pb-3">
        <h3 class="card-title" id="results_title">Hasil Laporan</h3>
    </div>
    <div class="card-body pt-0">
        {{-- Summary Stats --}}
        <div id="summary_stats" class="row g-3 mb-5" style="display:none;"></div>

        {{-- Invoice / Customer Table --}}
        <div id="results_table_wrapper" style="display:none;">
            <table id="results_table" class="table align-middle table-row-dashed table-row-gray-300 fs-6 gy-5">
                <thead id="results_table_head"></thead>
                <tbody id="results_table_body" class="fw-semibold text-gray-700"></tbody>
            </table>
        </div>

        {{-- Revenue Monthly Table --}}
        <div id="results_revenue" style="display:none;">
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">
                    <thead class="table-light">
                        <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                            <th>Bulan</th>
                            <th class="text-center">Total Tagihan</th>
                            <th class="text-center">Lunas</th>
                            <th class="text-end">Pendapatan</th>
                            <th class="text-end">Piutang</th>
                        </tr>
                    </thead>
                    <tbody id="revenue_monthly_body"></tbody>
                </table>
            </div>
        </div>

        {{-- Empty State --}}
        <div id="results_empty" class="text-center py-15 text-muted">
            <i class="ki-duotone ki-chart fs-3x mb-3" style="opacity:0.3;"><span></span></i>
            <p>Pilih filter dan klik "Tampilkan" untuk melihat laporan.</p>
        </div>

        {{-- Loading --}}
        <div id="results_loading" class="text-center py-15" style="display:none;">
            <span class="spinner-border spinner-border-sm align-middle text-primary me-2"></span>
            Memuat data...
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    "use strict";

    var formatRupiah = function (num) {
        if (!num && num !== 0) return '—';
        return 'Rp' + Number(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    };

    var formatDate = function (dateStr) {
        if (!dateStr) return '—';
        var d = new Date(dateStr);
        return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
    };

    var currentType = 'invoices';

    var statCard = function (label, value, icon, colorClass) {
        return '<div class="col-md-3 col-6">' +
            '<div class="card bg-light-' + colorClass + ' border-0 h-100">' +
            '<div class="card-body d-flex align-items-center gap-3 py-3">' +
            '<div class="flex-shrink-0">' +
            '<i class="ki-duotone ' + icon + ' fs-2x text-' + colorClass + '"></i>' +
            '</div>' +
            '<div>' +
            '<div class="text-muted small fw-semibold">' + label + '</div>' +
            '<div class="fw-bold fs-5 text-gray-900" id="stat_' + label.replace(/\s/g, '_') + '">' + value + '</div>' +
            '</div>' +
            '</div></div></div>';
    };

    var renderSummary = function (summary, type) {
        var html = '';
        if (type === 'revenue') {
            html += statCard('Total Tagihan', summary.total_count, 'ki-bill', 'primary');
            html += statCard('Lunas', summary.paid_count, 'ki-check-circle', 'success');
            html += statCard('Piutang', formatRupiah(summary.total_outstanding), 'ki-wallet', 'danger');
            html += statCard('Collection Rate', summary.collection_rate + '%', 'ki-chart-line-down', 'warning');
        } else if (type === 'invoices') {
            html += statCard('Total', summary.total_count, 'ki-bill', 'primary');
            html += statCard('Lunas', summary.paid_count, 'ki-check-circle', 'success');
            html += statCard('Belum Bayar', summary.unpaid_count, 'ki-wallet', 'warning');
            html += statCard('Jatuh Tempo', summary.overdue_count, 'ki-timer', 'danger');
        } else if (type === 'customers') {
            html += statCard('Total', summary.total_count, 'ki-users', 'primary');
            html += statCard('Aktif', summary.active_count, 'ki-check-circle', 'success');
            html += statCard('Isolir', summary.isolir_count, 'ki-shield-cross', 'warning');
            html += statCard('Nonaktif', summary.nonaktif_count, 'ki-ghost', 'dark');
        }
        document.getElementById('summary_stats').innerHTML = html;
        document.getElementById('summary_stats').style.display = '';
    };

    var renderInvoiceTable = function (data) {
        document.getElementById('results_table_head').innerHTML =
            '<tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">' +
            '<th>No. Tagihan</th><th>Pelanggan</th><th>Paket</th><th>Periode</th>' +
            '<th class="text-end">Jumlah</th><th>Jatuh Tempo</th><th>Status</th></tr>';
        document.getElementById('results_table_body').innerHTML = '';
        if (!data.length) {
            document.getElementById('results_table_body').innerHTML =
                '<tr><td colspan="7" class="text-center text-muted py-8">Tidak ada data.</td></tr>';
        } else {
            data.forEach(function (row) {
                var badgeClass = row.status === 'lunas' ? 'success' :
                    row.status === 'belum_bayar' ? 'danger' :
                    row.status === 'menunggu_verifikasi' ? 'warning' : 'dark';
                var overdueMark = row.is_overdue ? '<span class="badge badge-danger ms-1">Overdue</span>' : '';
                var tr = document.createElement('tr');
                tr.innerHTML =
                    '<td><span class="text-primary fw-bold">' + (row.invoice_number || '—') + '</span></td>' +
                    '<td><span class="text-gray-900 fw-semibold">' + (row.customer_name || '—') + '</span></td>' +
                    '<td><span class="text-gray-600">' + (row.package_name || '—') + '</span></td>' +
                    '<td><span class="text-gray-600">' + (row.billing_period || '—') + '</span></td>' +
                    '<td class="text-end"><span class="fw-bold text-gray-900">' + formatRupiah(row.amount) + '</span></td>' +
                    '<td><span class="text-gray-600">' + formatDate(row.due_date) + '</span></td>' +
                    '<td><span class="badge badge-' + badgeClass + '">' + (row.status_label || row.status) + '</span>' + overdueMark + '</td>';
                document.getElementById('results_table_body').appendChild(tr);
            });
        }
        document.getElementById('results_table_wrapper').style.display = '';
    };

    var renderCustomerTable = function (data) {
        document.getElementById('results_table_head').innerHTML =
            '<tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">' +
            '<th>Kode</th><th>Nama</th><th>Paket</th><th>Status</th>' +
            '<th class="text-center">Tagihan</th><th class="text-end">Total Bayar</th><th class="text-end">Sisa Tagihan</th></tr>';
        document.getElementById('results_table_body').innerHTML = '';
        if (!data.length) {
            document.getElementById('results_table_body').innerHTML =
                '<tr><td colspan="7" class="text-center text-muted py-8">Tidak ada data.</td></tr>';
        } else {
            data.forEach(function (row) {
                var badgeClass = row.status === 'aktif' ? 'success' :
                    row.status === 'isolir' ? 'warning' : 'dark';
                var tr = document.createElement('tr');
                tr.innerHTML =
                    '<td><span class="text-gray-600 small">' + (row.code || '—') + '</span></td>' +
                    '<td><span class="text-gray-900 fw-semibold">' + (row.name || '—') + '</span></td>' +
                    '<td><span class="text-gray-600">' + (row.package_name || '—') + '</span></td>' +
                    '<td><span class="badge badge-' + badgeClass + '">' + (row.status_label || row.status) + '</span></td>' +
                    '<td class="text-center"><span class="text-gray-700">' + row.total_invoices + ' (<span class="text-success">' + row.paid_invoices + '</span> lunas)</span></td>' +
                    '<td class="text-end"><span class="fw-bold text-success">' + formatRupiah(row.total_revenue) + '</span></td>' +
                    '<td class="text-end"><span class="fw-bold text-danger">' + formatRupiah(row.outstanding) + '</span></td>';
                document.getElementById('results_table_body').appendChild(tr);
            });
        }
        document.getElementById('results_table_wrapper').style.display = '';
    };

    var renderRevenueTable = function (monthly) {
        document.getElementById('revenue_monthly_body').innerHTML = '';
        if (!monthly.length) {
            document.getElementById('revenue_monthly_body').innerHTML =
                '<tr><td colspan="5" class="text-center text-muted py-8">Tidak ada data.</td></tr>';
        } else {
            monthly.forEach(function (row) {
                var tr = document.createElement('tr');
                tr.innerHTML =
                    '<td><span class="fw-semibold text-gray-900">' + row.month + '</span></td>' +
                    '<td class="text-center"><span class="badge badge-primary">' + row.total_count + '</span></td>' +
                    '<td class="text-center"><span class="badge badge-success">' + row.paid_count + '</span></td>' +
                    '<td class="text-end"><span class="fw-bold text-success">' + formatRupiah(row.paid_amount) + '</span></td>' +
                    '<td class="text-end"><span class="fw-bold text-danger">' + formatRupiah(row.outstanding_amount) + '</span></td>';
                document.getElementById('revenue_monthly_body').appendChild(tr);
            });
        }
        document.getElementById('results_revenue').style.display = '';
    };

    var generateReport = function () {
        var type = document.getElementById('report_type').value;
        var fromDate = document.getElementById('from_date').value;
        var toDate = document.getElementById('to_date').value;

        document.getElementById('summary_stats').style.display = 'none';
        document.getElementById('results_table_wrapper').style.display = 'none';
        document.getElementById('results_revenue').style.display = 'none';
        document.getElementById('results_empty').style.display = 'none';
        document.getElementById('results_loading').style.display = '';

        var title = document.getElementById('report_type').selectedOptions[0].text;
        document.getElementById('results_title').textContent = 'Hasil Laporan — ' + title;

        var url = "{{ route('admin.api.reports.generate', ['type' => '__TYPE__']) }}".replace('__TYPE__', type);
        var params = [];
        if (fromDate) params.push('from_date=' + fromDate);
        if (toDate) params.push('to_date=' + toDate);
        if (params.length) url += '?' + params.join('&');

        fetch(url, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            document.getElementById('results_loading').style.display = 'none';

            renderSummary(data.summary, type);

            if (type === 'revenue') {
                renderRevenueTable(data.monthly || []);
            } else if (type === 'invoices') {
                renderInvoiceTable(data.data || []);
            } else if (type === 'customers') {
                renderCustomerTable(data.data || []);
            }
        })
        .catch(function (err) {
            document.getElementById('results_loading').style.display = 'none';
            document.getElementById('results_empty').style.display = '';
            console.error('Gagal memuat laporan:', err);
        });
    };

    document.getElementById('btn_generate').addEventListener('click', generateReport);

    // Set default date range: first day of current month to today
    var today = new Date();
    var firstOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
    document.getElementById('to_date').value = today.toISOString().split('T')[0];
    document.getElementById('from_date').value = firstOfMonth.toISOString().split('T')[0];

    // Auto-generate on page load
    generateReport();
})();
</script>
@endpush
