@extends('layouts.app')

@section('title', 'Tagihan — K2-Net')

@section('toolbar')
    <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 my-0">Tagihan</h1>
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Home</a>
        </li>
        <li class="breadcrumb-item"><span class="text-muted">/</span></li>
        <li class="breadcrumb-item text-gray-900 fw-bold">Tagihan</li>
    </ul>
@endsection

@section('toolbar_actions')
    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_generate_invoice">
        <i class="ki-duotone ki-plus fs-2"><span class="path1"></span><span class="path2"></span></i>
        Buat Tagihan
    </button>
@endsection

@section('content')

<div class="row g-5 g-xl-10 mb-5">
    <div class="col-md-4">
        <div class="card card-flush h-xl-100" style="background: linear-gradient(135deg, #f14668 0%, #e53e3e 100%);">
            <div class="card-body pt-5 pb-4">
                <div class="d-flex align-items-center mb-3">
                    <i class="ki-duotone ki-document fs-2hx text-white opacity-75 me-3"></i>
                    <div>
                        <div class="fs-4 fw-bold text-white" id="stat_belum_bayar">0</div>
                        <div class="fs-7 fw-medium text-white text-opacity-75">Belum Bayar</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-flush h-xl-100" style="background: linear-gradient(135deg, #f6ad55 0%, #ed8936 100%);">
            <div class="card-body pt-5 pb-4">
                <div class="d-flex align-items-center mb-3">
                    <i class="ki-duotone ki-time-timer fs-2hx text-white opacity-75 me-3"></i>
                    <div>
                        <div class="fs-4 fw-bold text-white" id="stat_menunggu">0</div>
                        <div class="fs-7 fw-medium text-white text-opacity-75">Menunggu Verifikasi</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-flush h-xl-100" style="background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);">
            <div class="card-body pt-5 pb-4">
                <div class="d-flex align-items-center mb-3">
                    <i class="ki-duotone ki-check-circle fs-2hx text-white opacity-75 me-3"></i>
                    <div>
                        <div class="fs-4 fw-bold text-white" id="stat_lunas">0</div>
                        <div class="fs-7 fw-medium text-white text-opacity-75">Lunas</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card card-flush">
    <div class="card-header pt-5 pb-3">
        <div class="card-title w-100 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center position-relative my-1">
                <i class="ki-duotone ki-magnifier fs-1 position-absolute text-muted" style="z-index:1; left: 12px; top: 50%; transform: translateY(-50%); pointer-events: none;"></i>
                <input type="text" data-kt-invoices-table-filter="search"
                       class="form-control form-control-solid w-200px w-sm-250px ps-12"
                       placeholder="Cari tagihan..." />
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <select id="filter_status" class="form-select form-select-solid w-auto">
                    <option value="">Semua Status</option>
                    <option value="belum_bayar">Belum Bayar</option>
                    <option value="menunggu_verifikasi">Menunggu Verifikasi</option>
                    <option value="lunas">Lunas</option>
                    <option value="ditolak">Ditolak</option>
                </select>
            </div>
        </div>
    </div>
    <div class="card-body pt-0">
        <div class="table-responsive">
            <table id="kt_invoices_table" class="table align-middle table-row-dashed table-row-gray-300 fs-6 gy-5">
                <thead>
                    <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                        <th class="w-10px">#</th>
                        <th class="min-w-120px">No. Tagihan</th>
                        <th class="min-w-150px">Pelanggan</th>
                        <th class="min-w-100px">Periode</th>
                        <th class="min-w-100px">Jumlah</th>
                        <th class="min-w-100px">Jatuh Tempo</th>
                        <th class="min-w-80px">Status</th>
                        <th class="text-end min-w-70px">Aksi</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-700"></tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center flex-wrap mt-5 gap-3">
            <div id="kt_invoices_length"></div>
            <div id="kt_invoices_paginate"></div>
        </div>
    </div>
</div>

{{-- MODALS --}}

{{-- Generate Invoice Modal --}}
<div class="modal fade" tabindex="-1" id="kt_modal_generate_invoice">
    <div class="modal-dialog modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Buat Tagihan</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.api.invoices.generate') }}" id="generate_invoice_form">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Pelanggan <span class="text-danger">*</span></label>
                        <select name="customer_id" id="gen_customer_id" class="form-select" required>
                            <option value="">— Pilih Pelanggan —</option>
                        </select>
                        <div class="invalid-feedback" id="gen_customer_id_error"></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold">Periode Awal <span class="text-danger">*</span></label>
                            <input type="month" name="billing_period_start" id="gen_period_start"
                                   class="form-control" required />
                            <div class="invalid-feedback" id="gen_period_start_error"></div>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold">Periode Akhir <span class="text-danger">*</span></label>
                            <input type="month" name="billing_period_end" id="gen_period_end"
                                   class="form-control" required />
                            <div class="invalid-feedback" id="gen_period_end_error"></div>
                        </div>
                    </div>
                    <div class="alert alert-info d-flex align-items-center p-3">
                        <i class="ki-duotone ki-information-4 fs-2 me-2 text-info"><span class="path1"></span><span class="path2"></span></i>
                        <small>Maksimal 12 bulan sekaligus. Tagihan duplikat akan dilewati.</small>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-end">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="gen_invoice_submit">
                            <span class="spinner-border spinner-border-sm align-middle me-2 d-none" id="gen_invoice_spinner"></span>
                            Buat Tagihan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Invoice Modal --}}
<div class="modal fade" tabindex="-1" id="kt_modal_edit_invoice">
    <div class="modal-dialog modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Edit Tagihan</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="" id="edit_invoice_form">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">No. Tagihan</label>
                        <input type="text" id="edit_inv_number" class="form-control" readonly />
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Pelanggan</label>
                        <input type="text" id="edit_inv_customer" class="form-control" readonly />
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Periode Tagihan</label>
                        <input type="text" id="edit_inv_period" class="form-control" readonly />
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Jumlah (Rp) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" id="edit_inv_amount"
                               class="form-control" required min="0" step="1" />
                        <div class="invalid-feedback" id="edit_inv_amount_error"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Jatuh Tempo <span class="text-danger">*</span></label>
                        <input type="date" name="due_date" id="edit_inv_due_date"
                               class="form-control" required />
                        <div class="invalid-feedback" id="edit_inv_due_date_error"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                        <select name="status" id="edit_inv_status" class="form-select" required>
                            <option value="belum_bayar">Belum Bayar</option>
                            <option value="menunggu_verifikasi">Menunggu Verifikasi</option>
                            <option value="lunas">Lunas</option>
                            <option value="ditolak">Ditolak</option>
                        </select>
                        <div class="invalid-feedback" id="edit_inv_status_error"></div>
                    </div>
                    <div class="mb-3 d-none" id="edit_rejection_wrapper">
                        <label class="form-label fw-bold">Alasan Penolakan</label>
                        <textarea name="rejection_reason" id="edit_inv_rejection_reason"
                                  class="form-control" rows="2" maxlength="500"
                                  placeholder="Masukkan alasan penolakan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-end">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="edit_invoice_submit">
                            <span class="spinner-border spinner-border-sm align-middle me-2 d-none" id="edit_invoice_spinner"></span>
                            Simpan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- View Invoice Modal --}}
<div class="modal fade" tabindex="-1" id="kt_modal_view_invoice">
    <div class="modal-dialog modal-lg modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Detail Tagihan</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted fs-8 text-uppercase fw-semibold">No. Tagihan</label>
                            <div class="fw-bold text-gray-900" id="view_inv_number">—</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted fs-8 text-uppercase fw-semibold">Pelanggan</label>
                            <div class="fw-bold text-gray-900" id="view_inv_customer">—</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted fs-8 text-uppercase fw-semibold">Paket</label>
                            <div class="fw-bold text-gray-900" id="view_inv_package">—</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted fs-8 text-uppercase fw-semibold">Periode</label>
                            <div class="fw-bold text-gray-900" id="view_inv_period">—</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted fs-8 text-uppercase fw-semibold">Jumlah</label>
                            <div class="fw-bold text-gray-900 fs-5" id="view_inv_amount">—</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted fs-8 text-uppercase fw-semibold">Jatuh Tempo</label>
                            <div class="fw-bold text-gray-900" id="view_inv_due_date">—</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted fs-8 text-uppercase fw-semibold">Status</label>
                            <div id="view_inv_status">—</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted fs-8 text-uppercase fw-semibold">Tanggal Terbit</label>
                            <div class="fw-bold text-gray-900" id="view_inv_issued_at">—</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted fs-8 text-uppercase fw-semibold">Tanggal Bayar</label>
                            <div class="fw-bold text-gray-900" id="view_inv_paid_at">—</div>
                        </div>
                    </div>
                    <div class="col-12 d-none" id="view_rejection_wrapper">
                        <div class="alert alert-danger d-flex align-items-center p-3">
                            <i class="ki-duotone ki-information-4 fs-2 me-2"><span class="path1"></span><span class="path2"></span></i>
                            <div>
                                <div class="fs-8 fw-bold text-uppercase mb-1">Alasan Penolakan</div>
                                <div id="view_inv_rejection_reason">—</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    "use strict";

    var customersCache = [];

    var renderRowNumber = function (data, type, row, meta) {
        return '<span class="text-muted">' + (meta.row + meta.settings._iDisplayStart + 1) + '</span>';
    };

    var renderInvoiceNumber = function (num) {
        return '<span class="fw-bold text-primary">' + (num || '—') + '</span>';
    };

    var renderCustomer = function (name) {
        return name ? '<span class="fw-bold text-gray-900">' + name + '</span>' : '<span class="text-muted">—</span>';
    };

    var renderPeriod = function (period) {
        return '<span class="text-gray-600">' + (period || '—') + '</span>';
    };

    var renderAmount = function (amount) {
        return '<span class="fw-bold text-gray-900">' + amount + '</span>';
    };

    var renderDueDate = function (dueDate, isOverdue) {
        if (isOverdue) {
            return '<span class="text-danger fw-bold">' + dueDate + ' <i class="ki-duotone ki-warning-2 text-danger fs-8"><span></span><span></span></i></span>';
        }
        return '<span class="text-gray-600">' + dueDate + '</span>';
    };

    var renderStatus = function (status) {
        var map = {
            'belum_bayar': { cls: 'danger', label: 'Belum Bayar' },
            'menunggu_verifikasi': { cls: 'warning', label: 'Menunggu Verifikasi' },
            'lunas': { cls: 'success', label: 'Lunas' },
            'ditolak': { cls: 'dark', label: 'Ditolak' }
        };
        var s = map[status] || { cls: 'secondary', label: status || '—' };
        return '<span class="badge badge-light-' + s.cls + '">' + s.label + '</span>';
    };

    var renderActions = function (row) {
        if (!row || !row.actions) return '';
        var showUrl  = row.actions.show_url  || '';
        var editUrl  = row.actions.edit_url  || '';
        var deleteUrl = row.actions.delete_url || '';
        var html = '';
        html += '<button type="button" class="btn btn-sm btn-icon btn-light btn-active-light-primary me-1"';
        html += '        data-bs-toggle="tooltip" data-bs-placement="top" title="Lihat Detail"';
        html += '        onclick="openViewInvoiceModal(\'' + showUrl.replace(/'/g, "\\'") + '\')">';
        html += '    <i class="ki-duotone ki-eye fs-2"><span class="path1"></span><span class="path2"></span></i>';
        html += '</button>';
        html += '<button type="button" class="btn btn-sm btn-icon btn-light btn-active-light-primary me-1"';
        html += '        data-bs-toggle="tooltip" data-bs-placement="top" title="Edit"';
        html += '        onclick="openEditInvoiceModal(\'' + editUrl.replace(/'/g, "\\'") + '\')">';
        html += '    <i class="ki-duotone ki-pencil fs-2"><span class="path1"></span><span class="path2"></span></i>';
        html += '</button>';
        html += '<button type="button" class="btn btn-sm btn-icon btn-light btn-active-light-danger"';
        html += '        data-bs-toggle="tooltip" data-bs-placement="top" title="Hapus"';
        html += '        onclick="confirmDeleteInvoice(\'' + deleteUrl.replace(/'/g, "\\'") + '\')">';
        html += '    <i class="ki-duotone ki-trash fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>';
        html += '</button>';
        return html;
    };

    var clearGenErrors = function () {
        ['customer_id', 'period_start', 'period_end'].forEach(function (field) {
            var errEl = document.getElementById('gen_' + field + '_error');
            var inpEl = document.getElementById('gen_' + field);
            if (errEl) errEl.textContent = '';
            if (inpEl) inpEl.classList.remove('is-invalid');
        });
    };

    var clearEditErrors = function () {
        ['amount', 'due_date', 'status'].forEach(function (field) {
            var errEl = document.getElementById('edit_inv_' + field + '_error');
            var inpEl = document.getElementById('edit_inv_' + field);
            if (errEl) errEl.textContent = '';
            if (inpEl) inpEl.classList.remove('is-invalid');
        });
    };

    var drawCallback = function (settings) {
        var wrapper = document.querySelector('#kt_invoices_table_wrapper');
        if (!wrapper) return;
        var pageEl   = wrapper.querySelector('.dataTables_paginate');
        var lengthEl = wrapper.querySelector('.dataTables_length');
        if (lengthEl) {
            var selectEl = lengthEl.querySelector('select');
            if (selectEl) selectEl.className = 'form-select form-select-sm form-select-solid w-auto';
            var wrap = document.getElementById('kt_invoices_length');
            if (wrap) { wrap.innerHTML = lengthEl.outerHTML; lengthEl.remove(); }
        }
        if (pageEl) {
            var wrap = document.getElementById('kt_invoices_paginate');
            if (wrap) { wrap.innerHTML = pageEl.outerHTML; pageEl.remove(); }
        }
        var tooltips = document.querySelectorAll('#kt_invoices_table [data-bs-toggle="tooltip"]');
        tooltips.forEach(function (el) {
            if (!el._tooltip) { new bootstrap.Tooltip(el); }
        });
    };

    var initDataTable = function () {
        if (typeof jQuery === 'undefined' || !jQuery.fn.DataTable) return;
        var filterSearch = document.querySelector('[data-kt-invoices-table-filter="search"]');
        var dt = jQuery('#kt_invoices_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.api.invoices.datatable') }}",
                type: 'GET',
                data: function (d) {
                    var statusEl = document.getElementById('filter_status');
                    d.status = statusEl ? statusEl.value : '';
                }
            },
            columns: [
                { data: null,             name: null,           orderable: false, searchable: false },
                { data: 'invoice_number', name: 'invoice_number', orderable: true,  searchable: false },
                { data: 'customer_name',  name: 'customer_name',  orderable: true,  searchable: false },
                { data: 'billing_period', name: 'billing_period', orderable: true,  searchable: false },
                { data: 'formatted_amount', name: 'amount',     orderable: true,  searchable: false },
                { data: 'due_date',       name: 'due_date',       orderable: true,  searchable: false },
                { data: 'status',         name: 'status',         orderable: true,  searchable: false },
                { data: null,             name: null,             orderable: false, searchable: false, className: 'text-end' }
            ],
            columnDefs: [
                { targets: 0, render: renderRowNumber },
                { targets: 1, render: function (d) { return renderInvoiceNumber(d); } },
                { targets: 2, render: function (d) { return renderCustomer(d); } },
                { targets: 3, render: function (d) { return renderPeriod(d); } },
                { targets: 4, render: function (d) { return renderAmount(d); } },
                { targets: 5, render: function (d, t, row) { return renderDueDate(d, row.is_overdue); } },
                { targets: 6, render: function (d) { return renderStatus(d); } },
                { targets: 7, render: function (d, t, row) { return renderActions(row); } }
            ],
            drawCallback: drawCallback,
            order: [[1, 'desc']],
            searchDelay: 400,
            pagingType: 'simple_numbers',
            pageLength: 10,
            lengthChange: true,
            lengthMenu: [5, 10, 20, 50, 100],
            language: {
                processing: '<span class="spinner-border spinner-border-sm align-middle text-primary me-2"></span> Memuat...',
                lengthMenu: 'Tampilkan _MENU_',
                zeroRecords: 'Tidak ada tagihan',
                info: 'Menampilkan _START_–_END_ dari _TOTAL_ tagihan',
                infoEmpty: 'Menampilkan 0 dari 0 tagihan',
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
        window.invoicesTable = dt;

        if (filterSearch) {
            filterSearch.addEventListener('keyup', function (e) { dt.search(e.target.value).draw(); });
        }

        document.getElementById('filter_status').addEventListener('change', function () {
            dt.ajax.reload();
        });
    };

    window.openViewInvoiceModal = function (apiUrl) {
        fetch(apiUrl, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            document.getElementById('view_inv_number').textContent = data.invoice_number || '—';
            document.getElementById('view_inv_customer').textContent = data.customer?.name || '—';
            document.getElementById('view_inv_package').textContent = data.customer?.package?.name || '—';
            document.getElementById('view_inv_period').textContent = data.billing_period ? new Date(data.billing_period + '-01').toLocaleDateString('id-ID', { month: 'long', year: 'numeric' }) : '—';
            document.getElementById('view_inv_amount').textContent = data.formatted_amount || '—';
            document.getElementById('view_inv_due_date').textContent = data.due_date ? new Date(data.due_date).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }) : '—';
            document.getElementById('view_inv_status').innerHTML = renderStatus(data.status);
            document.getElementById('view_inv_issued_at').textContent = data.issued_at ? new Date(data.issued_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }) : '—';
            document.getElementById('view_inv_paid_at').textContent = data.paid_at ? new Date(data.paid_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }) : '—';
            var rejWrapper = document.getElementById('view_rejection_wrapper');
            if (data.rejection_reason) {
                rejWrapper.classList.remove('d-none');
                document.getElementById('view_inv_rejection_reason').textContent = data.rejection_reason;
            } else {
                rejWrapper.classList.add('d-none');
            }
            bootstrap.Modal.getOrCreateInstance(document.getElementById('kt_modal_view_invoice')).show();
        })
        .catch(function (err) { console.error('Gagal mengambil data tagihan:', err); });
    };

    window.openEditInvoiceModal = function (apiUrl) {
        clearEditErrors();
        fetch(apiUrl, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            var form = document.getElementById('edit_invoice_form');
            form.action = apiUrl;
            document.getElementById('edit_inv_number').value = data.invoice_number || '';
            document.getElementById('edit_inv_customer').value = data.customer?.name || '';
            document.getElementById('edit_inv_period').value = data.billing_period ? new Date(data.billing_period + '-01').toLocaleDateString('id-ID', { month: 'long', year: 'numeric' }) : '';
            document.getElementById('edit_inv_amount').value = data.amount || 0;
            document.getElementById('edit_inv_due_date').value = data.due_date || '';
            document.getElementById('edit_inv_status').value = data.status || 'belum_bayar';
            document.getElementById('edit_inv_rejection_reason').value = data.rejection_reason || '';
            toggleRejectionField(data.status);
            bootstrap.Modal.getOrCreateInstance(document.getElementById('kt_modal_edit_invoice')).show();
        })
        .catch(function (err) { console.error('Gagal mengambil data tagihan:', err); });
    };

    window.confirmDeleteInvoice = function (deleteUrl) {
        Swal.fire({
            text: "Yakin ingin menghapus tagihan ini?",
            icon: "warning",
            buttonsStyling: false,
            showCancelButton: true,
            confirmButtonText: "Hapus",
            cancelButtonText: "Batal",
            customClass: { confirmButton: "btn btn-danger", cancelButton: "btn btn-light" }
        }).then(function (result) {
            if (!result.isConfirmed) return;
            fetch(deleteUrl, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') || {content: ''}).content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(function (res) {
                if (res.status === 200 || res.status === 204) {
                    if (window.invoicesTable) window.invoicesTable.ajax.reload();
                    Swal.fire({ text: "Tagihan berhasil dihapus.", icon: "success", buttonsStyling: false, confirmButtonText: "Ok", customClass: { confirmButton: "btn btn-primary" } });
                } else {
                    res.json().then(function (data) {
                        Swal.fire({ text: data.message || "Gagal menghapus tagihan.", icon: "error", buttonsStyling: false, confirmButtonText: "Ok", customClass: { confirmButton: "btn btn-danger" } });
                    }).catch(function () {
                        Swal.fire({ text: "Terjadi kesalahan saat menghapus.", icon: "error", buttonsStyling: false, confirmButtonText: "Ok", customClass: { confirmButton: "btn btn-danger" } });
                    });
                }
            });
        });
    };

    var toggleRejectionField = function (status) {
        var wrapper = document.getElementById('edit_rejection_wrapper');
        if (status === 'ditolak') {
            wrapper.classList.remove('d-none');
        } else {
            wrapper.classList.add('d-none');
        }
    };

    var loadCustomers = function () {
        var select = document.getElementById('gen_customer_id');
        if (customersCache.length > 0) { renderCustomerOptions(select); return; }
        fetch("{{ route('admin.api.customers.datatable') }}", {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            customersCache = (data.data || []).map(function (r) { return { id: r.id, name: r.name }; });
            renderCustomerOptions(select);
        });
    };

    var renderCustomerOptions = function (select) {
        select.innerHTML = '<option value="">— Pilih Pelanggan —</option>';
        customersCache.forEach(function (c) {
            var opt = document.createElement('option');
            opt.value = c.id;
            opt.textContent = c.name;
            select.appendChild(opt);
        });
    };

    KTUtil.onDOMContentLoaded(function () {
        initDataTable();

        document.getElementById('kt_modal_generate_invoice').addEventListener('shown.bs.modal', loadCustomers);

        // Generate form
        var genForm = document.getElementById('generate_invoice_form');
        if (genForm) {
            genForm.addEventListener('submit', function (e) {
                e.preventDefault();
                clearGenErrors();
                var submitBtn = document.getElementById('gen_invoice_submit');
                var spinner   = document.getElementById('gen_invoice_spinner');
                var csrfEl    = document.querySelector('meta[name="csrf-token"]');
                submitBtn.disabled = true;
                spinner.classList.remove('d-none');
                var formData = new FormData(genForm);
                fetch(genForm.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfEl ? csrfEl.content : '',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                })
                .then(function (res) {
                    submitBtn.disabled = false;
                    spinner.classList.add('d-none');
                    if (res.status === 201 || res.status === 200) {
                        var modal = bootstrap.Modal.getInstance(document.getElementById('kt_modal_generate_invoice'));
                        if (modal) modal.hide();
                        genForm.reset();
                        if (window.invoicesTable) window.invoicesTable.ajax.reload();
                        res.json().then(function (data) {
                            Swal.fire({ text: data.message, icon: "success", buttonsStyling: false, confirmButtonText: "Ok", customClass: { confirmButton: "btn btn-primary" } });
                        });
                    } else {
                        res.json().then(function (data) {
                            if (data.errors) {
                                Object.keys(data.errors).forEach(function (field) {
                                    var errEl = document.getElementById('gen_' + field + '_error');
                                    var inpEl = document.getElementById('gen_' + field);
                                    if (errEl) { errEl.textContent = Array.isArray(data.errors[field]) ? data.errors[field][0] : data.errors[field]; errEl.style.display = 'block'; }
                                    if (inpEl) inpEl.classList.add('is-invalid');
                                });
                            }
                            Swal.fire({ text: data.message || "Gagal membuat tagihan.", icon: "error", buttonsStyling: false, confirmButtonText: "Ok", customClass: { confirmButton: "btn btn-danger" } });
                        }).catch(function () {
                            Swal.fire({ text: "Terjadi kesalahan saat membuat tagihan.", icon: "error", buttonsStyling: false, confirmButtonText: "Ok", customClass: { confirmButton: "btn btn-danger" } });
                        });
                    }
                })
                .catch(function () {
                    submitBtn.disabled = false;
                    spinner.classList.add('d-none');
                });
            });
        }

        // Edit form
        var editForm = document.getElementById('edit_invoice_form');
        if (editForm) {
            editForm.addEventListener('submit', function (e) {
                e.preventDefault();
                clearEditErrors();
                var submitBtn = document.getElementById('edit_invoice_submit');
                var spinner   = document.getElementById('edit_invoice_spinner');
                var csrfEl    = document.querySelector('meta[name="csrf-token"]');
                submitBtn.disabled = true;
                spinner.classList.remove('d-none');
                var formData = new FormData(editForm);
                fetch(editForm.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfEl ? csrfEl.content : '',
                        'X-HTTP-Method-Override': 'PUT',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                })
                .then(function (res) {
                    submitBtn.disabled = false;
                    spinner.classList.add('d-none');
                    if (res.status === 200 || res.status === 204) {
                        var modal = bootstrap.Modal.getInstance(document.getElementById('kt_modal_edit_invoice'));
                        if (modal) modal.hide();
                        if (window.invoicesTable) window.invoicesTable.ajax.reload();
                        Swal.fire({ text: "Tagihan berhasil diperbarui.", icon: "success", buttonsStyling: false, confirmButtonText: "Ok", customClass: { confirmButton: "btn btn-primary" } });
                    } else {
                        res.json().then(function (data) {
                            if (data.errors) {
                                Object.keys(data.errors).forEach(function (field) {
                                    var errEl = document.getElementById('edit_inv_' + field + '_error');
                                    var inpEl = document.getElementById('edit_inv_' + field);
                                    if (errEl) { errEl.textContent = Array.isArray(data.errors[field]) ? data.errors[field][0] : data.errors[field]; errEl.style.display = 'block'; }
                                    if (inpEl) inpEl.classList.add('is-invalid');
                                });
                            }
                            Swal.fire({ text: data.message || "Gagal memperbarui tagihan.", icon: "error", buttonsStyling: false, confirmButtonText: "Ok", customClass: { confirmButton: "btn btn-danger" } });
                        }).catch(function () {
                            Swal.fire({ text: "Terjadi kesalahan saat memperbarui.", icon: "error", buttonsStyling: false, confirmButtonText: "Ok", customClass: { confirmButton: "btn btn-danger" } });
                        });
                    }
                })
                .catch(function () {
                    submitBtn.disabled = false;
                    spinner.classList.add('d-none');
                });
            });

            document.getElementById('edit_inv_status').addEventListener('change', function () {
                toggleRejectionField(this.value);
            });
        }
    });

})();
</script>
@endpush
