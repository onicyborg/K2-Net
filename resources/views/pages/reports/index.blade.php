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

@section('toolbar_actions')
    <a href="#" id="btn_export_excel" class="btn btn-sm btn-light-success" style="display:none;">
        <i class="ki-duotone ki-tablet-ksd fs-2 me-1"><span></span></i>
        Export Excel
    </a>
@endsection

@section('content')

<div class="card card-flush mb-5">
    <div class="card-body py-5">
        <div class="row g-3 align-items-end">
            <div class="col-sm-6 col-lg-4">
                <label class="form-label fw-semibold text-gray-700">Jenis Laporan</label>
                <select id="report_type" class="form-select form-select-solid" onchange="Reports.reload()">
                    <option value="invoices">Tagihan</option>
                    <option value="revenue">Pendapatan</option>
                    <option value="customers">Pelanggan</option>
                </select>
            </div>
            <div class="col-6 col-lg-4">
                <label class="form-label fw-semibold text-gray-700">Dari Tanggal</label>
                <input type="date" id="from_date" class="form-control form-control-solid" onchange="Reports.reload()" />
            </div>
            <div class="col-6 col-lg-4">
                <label class="form-label fw-semibold text-gray-700">Sampai Tanggal</label>
                <input type="date" id="to_date" class="form-control form-control-solid" onchange="Reports.reload()" />
            </div>
        </div>
    </div>
</div>

<div id="summary_stats" class="row g-3 mb-5"></div>

<div id="wrapper_invoices" class="card card-flush" style="display:none;">
    <div class="card-body">
        <div class="table-responsive">
            <table id="table_invoices" class="table table-bordered">
                <thead>
                    <tr class="fw-bold fs-6 text-gray-800">
                        <th class="w-10px">#</th>
                        <th class="min-w-120px">No. Tagihan</th>
                        <th class="min-w-150px">Pelanggan</th>
                        <th class="min-w-100px">Paket</th>
                        <th class="min-w-100px">Periode</th>
                        <th class="text-end min-w-100px">Jumlah</th>
                        <th class="min-w-100px">Jatuh Tempo</th>
                        <th class="min-w-80px">Status</th>
                        <th class="min-w-100px">Lunas Pada</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-700"></tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center flex-wrap mt-5 gap-3">
            <div id="invoices_length"></div>
            <div id="invoices_paginate"></div>
        </div>
    </div>
</div>

<div id="wrapper_customers" class="card card-flush" style="display:none;">
    <div class="card-body">
        <div class="table-responsive">
            <table id="table_customers" class="table table-bordered">
                <thead>
                    <tr class="fw-bold fs-6 text-gray-800">
                        <th class="w-10px">#</th>
                        <th class="min-w-100px">Kode</th>
                        <th class="min-w-150px">Nama</th>
                        <th class="min-w-100px">Paket</th>
                        <th class="min-w-80px">Status</th>
                        <th class="text-center min-w-100px">Tagihan</th>
                        <th class="text-end min-w-120px">Total Bayar</th>
                        <th class="text-end min-w-120px">Sisa Tagihan</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-700"></tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center flex-wrap mt-5 gap-3">
            <div id="customers_length"></div>
            <div id="customers_paginate"></div>
        </div>
    </div>
</div>

<div id="wrapper_revenue" class="card card-flush" style="display:none;">
    <div class="card-body">
        <div class="table-responsive">
            <table id="table_revenue" class="table table-bordered">
                <thead>
                    <tr class="fw-bold fs-6 text-gray-800">
                        <th class="w-10px">#</th>
                        <th class="min-w-120px">Bulan</th>
                        <th class="text-center min-w-100px">Total Tagihan</th>
                        <th class="text-center min-w-80px">Lunas</th>
                        <th class="text-end min-w-120px">Pendapatan</th>
                        <th class="text-end min-w-120px">Piutang</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-700"></tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center flex-wrap mt-5 gap-3">
            <div id="revenue_length"></div>
            <div id="revenue_paginate"></div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    "use strict";

    var Reports = {
        dt: null,
        type: 'invoices',

        statCard: function (label, value, icon, colorClass) {
            return '<div class="col-md-3 col-6">' +
                '<div class="card bg-light-' + colorClass + ' border-0 h-100">' +
                '<div class="card-body d-flex align-items-center gap-3 py-3">' +
                '<div class="flex-shrink-0"><i class="ki-duotone ' + icon + ' fs-2x text-' + colorClass + '"></i></div>' +
                '<div>' +
                '<div class="text-muted small fw-semibold">' + label + '</div>' +
                '<div class="fw-bold fs-5 text-gray-900">' + value + '</div>' +
                '</div></div></div></div>';
        },

        formatRupiah: function (num) {
            if (num === null || num === undefined || num === '') return '—';
            return 'Rp ' + Number(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        },

        loadSummary: function () {
            var type = document.getElementById('report_type').value;
            var fromDate = document.getElementById('from_date').value;
            var toDate = document.getElementById('to_date').value;
            var url = "{{ route('admin.api.reports.summary', ['type' => '__TYPE__']) }}".replace('__TYPE__', type);
            var params = [];
            if (fromDate) params.push('from_date=' + fromDate);
            if (toDate) params.push('to_date=' + toDate);
            if (params.length) url += '?' + params.join('&');

            fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (res) { return res.json(); })
                .then(function (resp) {
                    var s = resp.summary || {};
                    var html = '';
                    if (type === 'revenue') {
                        html += this.statCard('Total Tagihan', s.total_count || 0, 'ki-bill', 'primary');
                        html += this.statCard('Lunas', s.paid_count || 0, 'ki-check-circle', 'success');
                        html += this.statCard('Piutang', this.formatRupiah(s.total_outstanding), 'ki-wallet', 'danger');
                        html += this.statCard('Collection Rate', (s.collection_rate || 0) + '%', 'ki-chart-line-down', 'warning');
                    } else if (type === 'invoices') {
                        html += this.statCard('Total', s.total_count || 0, 'ki-bill', 'primary');
                        html += this.statCard('Lunas', s.paid_count || 0, 'ki-check-circle', 'success');
                        html += this.statCard('Belum Bayar', s.unpaid_count || 0, 'ki-wallet', 'warning');
                        html += this.statCard('Jatuh Tempo', s.overdue_count || 0, 'ki-timer', 'danger');
                    } else if (type === 'customers') {
                        html += this.statCard('Total', s.total_count || 0, 'ki-users', 'primary');
                        html += this.statCard('Aktif', s.active_count || 0, 'ki-check-circle', 'success');
                        html += this.statCard('Isolir', s.isolir_count || 0, 'ki-shield-cross', 'warning');
                        html += this.statCard('Nonaktif', s.nonaktif_count || 0, 'ki-ghost', 'dark');
                    }
                    document.getElementById('summary_stats').innerHTML = html;
                }.bind(this));
        },

        getDatatableUrl: function () {
            var type = document.getElementById('report_type').value;
            var fromDate = document.getElementById('from_date').value;
            var toDate = document.getElementById('to_date').value;
            var baseUrl = "{{ route('admin.api.reports.datatable', ['type' => '__TYPE__']) }}".replace('__TYPE__', type);
            var params = [];
            if (fromDate) params.push('from_date=' + fromDate);
            if (toDate) params.push('to_date=' + toDate);
            return baseUrl + (params.length ? '?' + params.join('&') : '');
        },

        getExportUrl: function () {
            var type = document.getElementById('report_type').value;
            var fromDate = document.getElementById('from_date').value;
            var toDate = document.getElementById('to_date').value;
            var baseUrl = "{{ route('admin.api.reports.export', ['type' => '__TYPE__']) }}".replace('__TYPE__', type);
            var params = [];
            if (fromDate) params.push('from_date=' + fromDate);
            if (toDate) params.push('to_date=' + toDate);
            return baseUrl + (params.length ? '?' + params.join('&') : '');
        },

        reload: function () {
            var newType = document.getElementById('report_type').value;
            if (newType !== this.type) {
                this.type = newType;
                this.init();
            } else if (this.dt) {
                this.dt.ajax.url(this.getDatatableUrl()).load();
            }
            this.loadSummary();
        },

        init: function () {
            var self = this;
            var type = document.getElementById('report_type').value;

            var tableId, wrapperId, prefix;
            if (type === 'revenue') {
                tableId = 'table_revenue';
                wrapperId = 'wrapper_revenue';
                prefix = 'revenue';
            } else if (type === 'customers') {
                tableId = 'table_customers';
                wrapperId = 'wrapper_customers';
                prefix = 'customers';
            } else {
                tableId = 'table_invoices';
                wrapperId = 'wrapper_invoices';
                prefix = 'invoices';
            }

            ['wrapper_invoices', 'wrapper_customers', 'wrapper_revenue'].forEach(function (id) {
                document.getElementById(id).style.display = (id === wrapperId) ? '' : 'none';
            });

            if ($.fn.DataTable.isDataTable('#' + tableId)) {
                $('#' + tableId).DataTable().destroy();
            }

            var columns;
            if (type === 'revenue') {
                columns = [
                    { data: null, name: null, orderable: false, searchable: false },
                    { data: 'month', name: 'month', orderable: true, searchable: false },
                    { data: 'total_count', name: 'total_count', orderable: true, searchable: false },
                    { data: 'paid_count', name: 'paid_count', orderable: true, searchable: false },
                    { data: 'paid_amount', name: 'paid_amount', orderable: true, searchable: false },
                    { data: 'outstanding_amount', name: 'outstanding_amount', orderable: true, searchable: false },
                ];
            } else if (type === 'customers') {
                columns = [
                    { data: null, name: null, orderable: false, searchable: false },
                    { data: 'code', name: 'code', orderable: true, searchable: false },
                    { data: 'name', name: 'name', orderable: true, searchable: false },
                    { data: 'package_name', name: 'package_name', orderable: false, searchable: false },
                    { data: 'status', name: 'status', orderable: true, searchable: false },
                    { data: 'total_invoices', name: 'total_invoices', orderable: false, searchable: false },
                    { data: 'total_revenue', name: 'total_revenue', orderable: false, searchable: false },
                    { data: 'outstanding', name: 'outstanding', orderable: false, searchable: false },
                ];
            } else {
                columns = [
                    { data: null, name: null, orderable: false, searchable: false },
                    { data: 'invoice_number', name: 'invoice_number', orderable: true, searchable: false },
                    { data: 'customer_name', name: 'customer_name', orderable: true, searchable: false },
                    { data: 'package_name', name: 'package_name', orderable: false, searchable: false },
                    { data: 'billing_period', name: 'billing_period', orderable: true, searchable: false },
                    { data: 'formatted_amount', name: 'amount', orderable: true, searchable: false },
                    { data: 'due_date', name: 'due_date', orderable: true, searchable: false },
                    { data: 'status', name: 'status', orderable: true, searchable: false },
                    { data: 'paid_at', name: 'paid_at', orderable: true, searchable: false },
                ];
            }

            self.dt = $('#' + tableId).DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: self.getDatatableUrl(),
                    type: 'GET',
                    dataSrc: function (json) {
                        document.getElementById('btn_export_excel').style.display = '';
                        document.getElementById('btn_export_excel').href = self.getExportUrl();
                        return json.data || [];
                    },
                    error: function () {
                        console.error('DataTables AJAX error');
                    }
                },
                columns: columns,
                columnDefs: self.getColumnDefs(type),
                pagingType: 'simple_numbers',
                pageLength: 15,
                lengthMenu: [[15, 25, 50], [15, 25, 50]],
                order: [[1, 'asc']],
                searchDelay: 400,
                language: {
                    lengthMenu: 'Tampilkan _MENU_',
                    zeroRecords: 'Tidak ada data.',
                    info: 'Menampilkan _START_–_END_ dari _TOTAL_',
                    infoEmpty: 'Menampilkan 0 dari 0',
                    infoFiltered: '(disaring dari _MAX_ total)',
                    paginate: {
                        previous: '<i class="ki-duotone ki-left fs-3"><span class="path1"></span><span class="path2"></span></i>',
                        next: '<i class="ki-duotone ki-right fs-3"><span class="path1"></span><span class="path2"></span></i>'
                    },
                    processing: '<span class="spinner-border spinner-border-sm align-middle text-primary me-2"></span> Memuat...',
                },
                drawCallback: function () {
                    document.getElementById('btn_export_excel').href = self.getExportUrl();
                    if (document.getElementById(prefix + '_paginate').innerHTML.trim() !== '') return;
                    var wrapper = document.querySelector('#' + tableId + '_wrapper');
                    if (!wrapper) return;
                    var pageEl = wrapper.querySelector('.dataTables_paginate');
                    var lengthEl = wrapper.querySelector('.dataTables_length');
                    if (lengthEl) {
                        var selectEl = lengthEl.querySelector('select');
                        if (selectEl) selectEl.className = 'form-select form-select-sm form-select-solid w-auto';
                        document.getElementById(prefix + '_length').innerHTML = lengthEl.outerHTML;
                        lengthEl.remove();
                    }
                    if (pageEl) {
                        document.getElementById(prefix + '_paginate').innerHTML = pageEl.outerHTML;
                        pageEl.remove();
                    }
                }
            });
        },

        getColumnDefs: function (type) {
            var self = this;
            if (type === 'revenue') {
                return [
                    {
                        targets: 0, render: function (data, type, row, meta) {
                            return '<span class="text-muted">' + (meta.row + meta.settings._iDisplayStart + 1) + '</span>';
                        }
                    },
                    {
                        targets: 1, render: function (d) { return '<span class="fw-bold text-gray-900">' + (d || '—') + '</span>'; }
                    },
                    {
                        targets: 2, render: function (d) {
                            return '<span class="badge badge-primary">' + d + '</span>';
                        }
                    },
                    {
                        targets: 3, render: function (d) {
                            return '<span class="badge badge-success">' + d + '</span>';
                        }
                    },
                    {
                        targets: 4, render: function (d) {
                            return '<span class="fw-bold text-success">' + self.formatRupiah(d) + '</span>';
                        }
                    },
                    {
                        targets: 5, render: function (d) {
                            return '<span class="fw-bold text-danger">' + self.formatRupiah(d) + '</span>';
                        }
                    },
                ];
            } else if (type === 'customers') {
                return [
                    {
                        targets: 0, render: function (data, type, row, meta) {
                            return '<span class="text-muted">' + (meta.row + meta.settings._iDisplayStart + 1) + '</span>';
                        }
                    },
                    {
                        targets: 1, render: function (d) { return '<span class="text-gray-600">' + (d || '—') + '</span>'; }
                    },
                    {
                        targets: 2, render: function (d) { return '<span class="fw-bold text-gray-900">' + (d || '—') + '</span>'; }
                    },
                    {
                        targets: 3, render: function (d) {
                            return d ? '<span class="badge badge-light-primary">' + d + '</span>' : '<span class="text-muted">—</span>';
                        }
                    },
                    {
                        targets: 4, render: function (d) {
                            var map = { 'aktif': 'success', 'isolir': 'warning', 'nonaktif': 'dark' };
                            var cls = map[d] || 'secondary';
                            var label = { 'aktif': 'Aktif', 'isolir': 'Isolir', 'nonaktif': 'Nonaktif' }[d] || d || '—';
                            return '<span class="badge badge-light-' + cls + '">' + label + '</span>';
                        }
                    },
                    {
                        targets: 5, render: function (d, type, row) {
                            return d + ' (<span class="text-success">' + (row.paid_invoices || 0) + '</span> lunas)';
                        }
                    },
                    {
                        targets: 6, className: 'text-end', render: function (d) {
                            return '<span class="fw-bold text-success">' + self.formatRupiah(d) + '</span>';
                        }
                    },
                    {
                        targets: 7, className: 'text-end', render: function (d) {
                            return '<span class="fw-bold text-danger">' + self.formatRupiah(d) + '</span>';
                        }
                    },
                ];
            } else {
                return [
                    {
                        targets: 0, render: function (data, type, row, meta) {
                            return '<span class="text-muted">' + (meta.row + meta.settings._iDisplayStart + 1) + '</span>';
                        }
                    },
                    {
                        targets: 1, render: function (d) { return '<span class="text-gray-600">' + (d || '—') + '</span>'; }
                    },
                    {
                        targets: 2, render: function (d) { return '<span class="fw-bold text-gray-900">' + (d || '—') + '</span>'; }
                    },
                    {
                        targets: 3, render: function (d) {
                            return d ? '<span class="badge badge-light-primary">' + d + '</span>' : '<span class="text-muted">—</span>';
                        }
                    },
                    {
                        targets: 4, render: function (d) { return '<span class="text-gray-600">' + (d || '—') + '</span>'; }
                    },
                    {
                        targets: 5, className: 'text-end', render: function (d) {
                            return '<span class="fw-bold text-gray-900">' + (d || '—') + '</span>';
                        }
                    },
                    {
                        targets: 6, render: function (d) { return '<span class="text-gray-600">' + (d || '—') + '</span>'; }
                    },
                    {
                        targets: 7, render: function (d, type, row) {
                            var map = { 'lunas': 'success', 'belum_bayar': 'danger', 'menunggu_verifikasi': 'warning', 'ditolak': 'dark' };
                            var cls = map[d] || 'secondary';
                            var label = { 'lunas': 'Lunas', 'belum_bayar': 'Belum Bayar', 'menunggu_verifikasi': 'Menunggu Verifikasi', 'ditolak': 'Ditolak' }[d] || d || '—';
                            return '<span class="badge badge-light-' + cls + '">' + label + '</span>';
                        }
                    },
                    {
                        targets: 8, render: function (d) { return '<span class="text-gray-600">' + (d || '—') + '</span>'; }
                    },
                ];
            }
        }
    };

    window.Reports = Reports;

    var today = new Date();
    var firstOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
    document.getElementById('to_date').value = today.toISOString().split('T')[0];
    document.getElementById('from_date').value = firstOfMonth.toISOString().split('T')[0];

    document.getElementById('btn_export_excel').addEventListener('click', function (e) {
        if (!this.href || this.href === '#') {
            e.preventDefault();
            alert('Tunggu data selesai dimuat.');
        }
    });

    Reports.init();
    Reports.loadSummary();
})();
</script>
@endpush
