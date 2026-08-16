@extends('layouts.app')

@section('title', 'Log Notifikasi — K2-Net')

@section('toolbar')
    <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 my-0">Log Notifikasi</h1>
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Home</a>
        </li>
        <li class="breadcrumb-item"><span class="text-muted">/</span></li>
        <li class="breadcrumb-item text-gray-900 fw-bold">Log Notifikasi</li>
    </ul>
@endsection

@section('content')

<div class="card card-flush">
    <div class="card-header pt-5 pb-3">
        <div class="card-title w-100 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center position-relative my-1">
                <i class="ki-duotone ki-magnifier fs-1 position-absolute text-muted" style="z-index:1; left: 12px; top: 50%; transform: translateY(-50%); pointer-events: none;"></i>
                <input type="text" data-kt-notifications-table-filter="search"
                       class="form-control form-control-solid w-200px w-sm-250px ps-12"
                       placeholder="Cari pelanggan..." />
            </div>
        </div>
    </div>
    <div class="card-body pt-0">
        <div class="table-responsive">
            <table id="kt_notifications_table" class="table align-middle table-row-dashed table-row-gray-300 fs-6 gy-5">
                <thead>
                    <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                        <th class="w-10px">#</th>
                        <th class="min-w-150px">Pelanggan</th>
                        <th class="min-w-100px">No. Tagihan</th>
                        <th class="min-w-120px">Tipe</th>
                        <th class="min-w-80px">Channel</th>
                        <th class="min-w-80px">Status</th>
                        <th class="min-w-150px">Tanggal Kirim</th>
                        <th class="min-w-200px">Error</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-700"></tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center flex-wrap mt-5 gap-3">
            <div id="kt_notifications_length"></div>
            <div id="kt_notifications_paginate"></div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    "use strict";

    var formatDate = function (dateStr) {
        if (!dateStr) return '<span class="text-muted">—</span>';
        return dateStr;
    };

    var renderRowNumber = function (data, type, row, meta) {
        return '<span class="text-muted">' + (meta.row + meta.settings._iDisplayStart + 1) + '</span>';
    };

    var renderCustomer = function (row) {
        if (!row.customer_name) return '<span class="text-muted">—</span>';
        return '<div><span class="fw-bold text-gray-900">' + row.customer_name + '</span><br><small class="text-muted">' + (row.customer_code || '') + '</small></div>';
    };

    var renderInvoice = function (num) {
        return num ? '<span class="text-primary fw-semibold">' + num + '</span>' : '<span class="text-muted">—</span>';
    };

    var renderType = function (row) {
        var typeColors = {
            'reminder_h3':      'primary',
            'reminder_before':   'warning',
            'reminder_overdue':  'danger',
            'confirmation':      'success',
            'rejection':         'dark'
        };
        var color = typeColors[row.type] || 'secondary';
        return '<span class="badge badge-' + color + '">' + (row.type_label || row.type) + '</span>';
    };

    var renderChannel = function (ch, label) {
        var icon = ch === 'whatsapp' ? 'ki-message-text' : 'ki-mail';
        var color = ch === 'whatsapp' ? 'success' : 'primary';
        return '<span class="badge badge-' + color + '"><i class="ki-duotone ' + icon + ' me-1"></i>' + (label || ch) + '</span>';
    };

    var renderStatus = function (row) {
        return '<span class="badge badge-' + (row.status_class || 'secondary') + '">' + (row.status_label || row.status) + '</span>';
    };

    var renderError = function (msg) {
        if (!msg) return '<span class="text-muted">—</span>';
        return '<span class="text-danger small" data-bs-toggle="tooltip" title="' + msg + '">' + msg.substring(0, 50) + (msg.length > 50 ? '...' : '') + '</span>';
    };

    var drawCallback = function (settings) {
        var wrapper = document.querySelector('#kt_notifications_table_wrapper');
        if (!wrapper) return;
        var pageEl   = wrapper.querySelector('.dataTables_paginate');
        var lengthEl = wrapper.querySelector('.dataTables_length');
        if (lengthEl) {
            var selectEl = lengthEl.querySelector('select');
            if (selectEl) selectEl.className = 'form-select form-select-sm form-select-solid w-auto';
            var wrap = document.getElementById('kt_notifications_length');
            if (wrap) { wrap.innerHTML = lengthEl.outerHTML; lengthEl.remove(); }
        }
        if (pageEl) {
            var wrap = document.getElementById('kt_notifications_paginate');
            if (wrap) { wrap.innerHTML = pageEl.outerHTML; pageEl.remove(); }
        }
        var tooltips = document.querySelectorAll('#kt_notifications_table [data-bs-toggle="tooltip"]');
        tooltips.forEach(function (el) {
            if (!el._tooltip) { new bootstrap.Tooltip(el); }
        });
    };

    var initDataTable = function () {
        if (typeof jQuery === 'undefined' || !jQuery.fn.DataTable) return;
        var filterSearch = document.querySelector('[data-kt-notifications-table-filter="search"]');
        var dt = jQuery('#kt_notifications_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.api.notifications.datatable') }}",
                type: 'GET',
                data: function (d) {}
            },
            columns: [
                { data: null,             name: null,             orderable: false, searchable: false },
                { data: 'customer_name',  name: 'customer_name', orderable: true,  searchable: true },
                { data: 'invoice_number', name: 'invoice_number', orderable: true,  searchable: false },
                { data: 'type',           name: null,           orderable: false, searchable: false },
                { data: 'channel',        name: null,           orderable: false, searchable: false },
                { data: 'status',         name: null,           orderable: false, searchable: false },
                { data: 'sent_at',        name: 'sent_at',      orderable: true,  searchable: false },
                { data: 'error_message',  name: null,           orderable: false, searchable: false },
            ],
            columnDefs: [
                { targets: 0, render: renderRowNumber },
                { targets: 1, render: function (d, t, row) { return renderCustomer(row); } },
                { targets: 2, render: renderInvoice },
                { targets: 3, render: renderType },
                { targets: 4, render: function (d, t, row) { return renderChannel(row.channel, row.channel_label); } },
                { targets: 5, render: renderStatus },
                { targets: 6, render: function (d) { return formatDate(d); } },
                { targets: 7, render: renderError },
            ],
            drawCallback: drawCallback,
            order: [[6, 'desc']],
            searchDelay: 400,
            pagingType: 'simple_numbers',
            pageLength: 10,
            lengthChange: true,
            lengthMenu: [10, 25, 50, 100],
            language: {
                processing: '<span class="spinner-border spinner-border-sm align-middle text-primary me-2"></span> Memuat...',
                lengthMenu: 'Tampilkan _MENU_',
                zeroRecords: 'Tidak ada log notifikasi',
                info: 'Menampilkan _START_–_END_ dari _TOTAL_',
                infoEmpty: 'Menampilkan 0 dari 0',
                infoFiltered: '(disaring dari _MAX_ total)',
                search: '',
                paginate: {
                    previous: '<i class="ki-duotone ki-left fs-3"><span class="path1"></span><span class="path2"></span></i>',
                    next:     '<i class="ki-duotone ki-right fs-3"><span class="path1"></span><span class="path2"></span></i>'
                }
            },
            dom: "<'row'<'col-sm-12'tr>>" +
                 "<'row mt-5'<'col-sm-12 col-md-5 d-flex align-items-center justify-content-center justify-content-md-start'l>" +
                 "<'col-sm-12 col-md-7 d-flex align-items-center justify-content-center justify-content-md-end'p>>"
        });
        window.notificationsTable = dt;

        if (filterSearch) {
            filterSearch.addEventListener('keyup', function (e) { dt.search(e.target.value).draw(); });
        }
    };

    KTUtil.onDOMContentLoaded(function () {
        initDataTable();
    });
})();
</script>
@endpush
