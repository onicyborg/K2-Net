@extends('layouts.app')

@section('title', 'Verifikasi Pembayaran — K2-Net')

@section('toolbar')
    <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 my-0">Verifikasi Pembayaran</h1>
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Home</a>
        </li>
        <li class="breadcrumb-item"><span class="text-muted">/</span></li>
        <li class="breadcrumb-item text-gray-900 fw-bold">Verifikasi Pembayaran</li>
    </ul>
@endsection

@section('content')

<div class="alert alert-warning d-flex align-items-center p-3 mb-5">
    <i class="ki-duotone ki-information-4 fs-2 me-3 text-warning"><span class="path1"></span><span class="path2"></span></i>
    <div>
        <strong>Halaman ini menampilkan</strong> tagihan dengan status <span class="badge badge-light-warning">Menunggu Verifikasi</span>.
        Upload bukti pembayaran dilakukan oleh pelanggan melalui Portal Pelanggan.
    </div>
</div>

<div class="card card-flush">
    <div class="card-header pt-5 pb-3">
        <div class="card-title w-100 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center position-relative my-1">
                <i class="ki-duotone ki-magnifier fs-1 position-absolute text-muted" style="z-index:1; left: 12px; top: 50%; transform: translateY(-50%); pointer-events: none;"></i>
                <input type="text" data-kt-verifications-table-filter="search"
                       class="form-control form-control-solid w-250px ps-12"
                       placeholder="Cari..." />
            </div>
        </div>
    </div>
    <div class="card-body pt-0">
        <table id="kt_verifications_table" class="table align-middle table-row-dashed table-row-gray-300 fs-6 gy-5">
            <thead>
                <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                    <th class="w-10px">#</th>
                    <th class="min-w-120px">No. Tagihan</th>
                    <th class="min-w-150px">Pelanggan</th>
                    <th class="min-w-100px">Periode</th>
                    <th class="min-w-100px">Jumlah</th>
                    <th class="min-w-150px">Bukti Bayar</th>
                    <th class="min-w-120px">Tanggal Upload</th>
                    <th class="text-end min-w-100px">Aksi</th>
                </tr>
            </thead>
            <tbody class="fw-semibold text-gray-700"></tbody>
        </table>
        <div id="kt_verifications_pagination" class="d-flex justify-content-between align-items-center flex-wrap mt-5 gap-3">
            <div class="d-flex align-items-center gap-3" id="kt_verifications_length"></div>
            <div class="d-flex align-items-center" id="kt_verifications_info"></div>
            <div id="kt_verifications_paginate"></div>
        </div>
    </div>
</div>

{{-- View Detail Modal --}}
<div class="modal fade" tabindex="-1" id="kt_modal_view_verification">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Detail Verifikasi</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted fs-8 text-uppercase fw-semibold">No. Tagihan</label>
                            <div class="fw-bold text-gray-900" id="view_ver_inv_number">—</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted fs-8 text-uppercase fw-semibold">Pelanggan</label>
                            <div class="fw-bold text-gray-900" id="view_ver_customer">—</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted fs-8 text-uppercase fw-semibold">Paket</label>
                            <div class="fw-bold text-gray-900" id="view_ver_package">—</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted fs-8 text-uppercase fw-semibold">Periode</label>
                            <div class="fw-bold text-gray-900" id="view_ver_period">—</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted fs-8 text-uppercase fw-semibold">Jumlah Tagihan</label>
                            <div class="fw-bold text-gray-900 fs-5" id="view_ver_amount">—</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted fs-8 text-uppercase fw-semibold">Jatuh Tempo</label>
                            <div class="fw-bold text-gray-900" id="view_ver_due_date">—</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted fs-8 text-uppercase fw-semibold">Tanggal Upload Bukti</label>
                            <div class="fw-bold text-gray-900" id="view_ver_uploaded_at">—</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="form-label text-muted fs-8 text-uppercase fw-semibold">Bukti Pembayaran</label>
                            <div id="view_ver_proof_container">—</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-danger" id="btn_reject_verification">
                    <i class="ki-duotone ki-close fs-2 me-1"><span class="path1"></span><span class="path2"></span></i>
                    Tolak
                </button>
                <button type="button" class="btn btn-success" id="btn_approve_verification">
                    <i class="ki-duotone ki-check fs-2 me-1"><span class="path1"></span><span class="path2"></span></i>
                    Setujui
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Reject Modal --}}
<div class="modal fade" tabindex="-1" id="kt_modal_reject_verification">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title text-danger">Tolak Pembayaran</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="reject_verification_form">
                <div class="modal-body">
                    <div class="alert alert-warning d-flex align-items-center p-3 mb-3">
                        <i class="ki-duotone ki-information-4 fs-2 me-2"><span class="path1"></span><span class="path2"></span></i>
                        <small>Tagihan akan dikembalikan ke status <strong>Belum Bayar</strong>.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" id="reject_reason" class="form-control"
                                  rows="3" maxlength="500" required
                                  placeholder="Jelaskan alasan penolakan..."></textarea>
                        <div class="invalid-feedback" id="reject_reason_error"></div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger" id="reject_submit_btn">
                        <span class="spinner-border spinner-border-sm align-middle me-2 d-none" id="reject_spinner"></span>
                        Tolak Pembayaran
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    "use strict";

    var currentInvoiceId = null;
    var currentApproveUrl = null;
    var currentRejectUrl = null;

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

    var renderProof = function (proof) {
        if (!proof) return '<span class="text-muted">—</span>';
        return '<span class="badge badge-light-primary">' + proof.file_name + '</span>';
    };

    var renderUploadedAt = function (dt) {
        return '<span class="text-gray-600">' + (dt || '—') + '</span>';
    };

    var renderActions = function (row) {
        if (!row || !row.actions) return '';
        var showUrl = row.actions.show_url || '';
        var html = '';
        html += '<button type="button" class="btn btn-sm btn-light-primary me-1"';
        html += '        data-bs-toggle="tooltip" data-bs-placement="top" title="Lihat & Verifikasi"';
        html += '        onclick="openVerificationModal(\'' + showUrl.replace(/'/g, "\\'") + '\')">';
        html += '    <i class="ki-duotone ki-eye fs-2 me-1"><span class="path1"></span><span class="path2"></span></i>';
        html += '    Verifikasi';
        html += '</button>';
        return html;
    };

    var drawCallback = function (settings) {
        var wrapper = document.querySelector('#kt_verifications_table_wrapper');
        if (!wrapper) return;
        var infoEl   = wrapper.querySelector('.dataTables_info');
        var pageEl   = wrapper.querySelector('.dataTables_paginate');
        var lengthEl = wrapper.querySelector('.dataTables_length');
        if (lengthEl) {
            var selectEl = lengthEl.querySelector('select');
            if (selectEl) selectEl.className = 'form-select form-select-sm form-select-solid w-auto';
            var wrap = document.getElementById('kt_verifications_length');
            if (wrap) { wrap.innerHTML = lengthEl.outerHTML; lengthEl.remove(); }
        }
        if (infoEl) {
            var wrap = document.getElementById('kt_verifications_info');
            if (wrap) { wrap.innerHTML = infoEl.outerHTML; infoEl.remove(); }
        }
        if (pageEl) {
            var wrap = document.getElementById('kt_verifications_paginate');
            if (wrap) { wrap.innerHTML = pageEl.outerHTML; pageEl.remove(); }
        }
        var tooltips = document.querySelectorAll('#kt_verifications_table [data-bs-toggle="tooltip"]');
        tooltips.forEach(function (el) {
            if (!el._tooltip) { new bootstrap.Tooltip(el); }
        });
    };

    var initDataTable = function () {
        if (typeof jQuery === 'undefined' || !jQuery.fn.DataTable) return;
        var filterSearch = document.querySelector('[data-kt-verifications-table-filter="search"]');
        var dt = jQuery('#kt_verifications_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.api.verifications.datatable') }}",
                type: 'GET',
                data: function (d) {}
            },
            columns: [
                { data: null,            name: null,            orderable: false, searchable: false },
                { data: 'invoice_number', name: 'invoice_number', orderable: true,  searchable: false },
                { data: 'customer_name',  name: 'customer_name',  orderable: true,  searchable: false },
                { data: 'billing_period', name: 'billing_period', orderable: true,  searchable: false },
                { data: 'formatted_amount', name: 'amount',      orderable: true,  searchable: false },
                { data: 'payment_proof',  name: 'payment_proof',  orderable: false, searchable: false },
                { data: 'submitted_at',   name: 'submitted_at',   orderable: true,  searchable: false },
                { data: null,             name: null,             orderable: false, searchable: false, className: 'text-end' }
            ],
            columnDefs: [
                { targets: 0, render: renderRowNumber },
                { targets: 1, render: function (d) { return renderInvoiceNumber(d); } },
                { targets: 2, render: function (d) { return renderCustomer(d); } },
                { targets: 3, render: function (d) { return renderPeriod(d); } },
                { buttons: 4, render: function (d) { return renderAmount(d); } },
                { targets: 5, render: function (d) { return renderProof(d); } },
                { targets: 6, render: function (d) { return renderUploadedAt(d); } },
                { targets: 7, render: function (d, t, row) { return renderActions(row); } }
            ],
            drawCallback: drawCallback,
            order: [[6, 'desc']],
            searchDelay: 400,
            pagingType: 'simple_numbers',
            pageLength: 10,
            lengthChange: true,
            lengthMenu: [5, 10, 20, 50],
            language: {
                processing: '<span class="spinner-border spinner-border-sm align-middle text-primary me-2"></span> Memuat...',
                lengthMenu: 'Tampilkan _MENU_',
                zeroRecords: 'Tidak ada pembayaran yang menunggu verifikasi',
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
        window.verificationsTable = dt;

        if (filterSearch) {
            filterSearch.addEventListener('keyup', function (e) { dt.search(e.target.value).draw(); });
        }
    };

    window.openVerificationModal = function (apiUrl) {
        fetch(apiUrl, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            currentInvoiceId   = data.id;
            currentApproveUrl  = apiUrl.replace('/show', '/approve');
            currentRejectUrl   = apiUrl.replace('/show', '/reject');

            document.getElementById('view_ver_inv_number').textContent = data.invoice_number || '—';
            document.getElementById('view_ver_customer').textContent = data.customer?.name || '—';
            document.getElementById('view_ver_package').textContent = data.customer?.package?.name || '—';
            document.getElementById('view_ver_period').textContent = data.billing_period ? new Date(data.billing_period + '-01').toLocaleDateString('id-ID', { month: 'long', year: 'numeric' }) : '—';
            document.getElementById('view_ver_amount').textContent = data.formatted_amount || '—';
            document.getElementById('view_ver_due_date').textContent = data.due_date ? new Date(data.due_date).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }) : '—';
            document.getElementById('view_ver_uploaded_at').textContent = data.payment_proof?.uploaded_at ? new Date(data.payment_proof.uploaded_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '—';

            var proofContainer = document.getElementById('view_ver_proof_container');
            if (data.payment_proof) {
                proofContainer.innerHTML = '<div class="d-flex align-items-center gap-3">' +
                    '<span class="badge badge-light-primary fs-6"><i class="ki-duotone ki-picture fs-2 me-1"><span></span></i> ' + data.payment_proof.file_name + '</span>' +
                    '</div>';
            } else {
                proofContainer.innerHTML = '<span class="text-muted">Tidak ada bukti pembayaran.</span>';
            }

            bootstrap.Modal.getOrCreateInstance(document.getElementById('kt_modal_view_verification')).show();
        })
        .catch(function (err) { console.error('Gagal mengambil data:', err); });
    };

    var handleVerificationAction = function (url, method, body, successMsg) {
        fetch(url, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') || {content: ''}).content,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(body || {}),
        })
        .then(function (res) {
            if (res.status === 200 || res.status === 201) {
                var modal = bootstrap.Modal.getInstance(document.getElementById('kt_modal_view_verification'));
                if (modal) modal.hide();
                if (window.verificationsTable) window.verificationsTable.ajax.reload();
                Swal.fire({ text: successMsg, icon: "success", buttonsStyling: false, confirmButtonText: "Ok", customClass: { confirmButton: "btn btn-primary" } });
            } else {
                res.json().then(function (data) {
                    Swal.fire({ text: data.message || "Terjadi kesalahan.", icon: "error", buttonsStyling: false, confirmButtonText: "Ok", customClass: { confirmButton: "btn btn-danger" } });
                }).catch(function () {
                    Swal.fire({ text: "Terjadi kesalahan.", icon: "error", buttonsStyling: false, confirmButtonText: "Ok", customClass: { confirmButton: "btn btn-danger" } });
                });
            }
        })
        .catch(function () {
            Swal.fire({ text: "Terjadi kesalahan koneksi.", icon: "error", buttonsStyling: false, confirmButtonText: "Ok", customClass: { confirmButton: "btn btn-danger" } });
        });
    };

    KTUtil.onDOMContentLoaded(function () {
        initDataTable();

        // Approve button
        document.getElementById('btn_approve_verification').addEventListener('click', function () {
            if (!currentApproveUrl) return;
            Swal.fire({
                text: "Setujui pembayaran ini? Tagihan akan ditandai lunas.",
                icon: "success",
                buttonsStyling: false,
                showCancelButton: true,
                confirmButtonText: "Ya, Setujui",
                cancelButtonText: "Batal",
                customClass: { confirmButton: "btn btn-success", cancelButton: "btn btn-light" }
            }).then(function (result) {
                if (!result.isConfirmed) return;
                handleVerificationAction(currentApproveUrl, 'POST', {}, "Pembayaran berhasil disetujui.");
            });
        });

        // Reject button — open reject modal
        document.getElementById('btn_reject_verification').addEventListener('click', function () {
            var viewModal = bootstrap.Modal.getInstance(document.getElementById('kt_modal_view_verification'));
            if (viewModal) viewModal.hide();
            document.getElementById('reject_reason').value = '';
            document.getElementById('reject_reason').classList.remove('is-invalid');
            bootstrap.Modal.getOrCreateInstance(document.getElementById('kt_modal_reject_verification')).show();
        });

        // Reject form submit
        document.getElementById('reject_verification_form').addEventListener('submit', function (e) {
            e.preventDefault();
            var reasonEl = document.getElementById('reject_reason');
            var reason = reasonEl.value.trim();
            if (!reason) {
                reasonEl.classList.add('is-invalid');
                return;
            }
            reasonEl.classList.remove('is-invalid');
            var submitBtn = document.getElementById('reject_submit_btn');
            var spinner = document.getElementById('reject_spinner');
            submitBtn.disabled = true;
            spinner.classList.remove('d-none');
            fetch(currentRejectUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') || {content: ''}).content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ rejection_reason: reason }),
            })
            .then(function (res) {
                submitBtn.disabled = false;
                spinner.classList.add('d-none');
                if (res.status === 200 || res.status === 201) {
                    var modal = bootstrap.Modal.getInstance(document.getElementById('kt_modal_reject_verification'));
                    if (modal) modal.hide();
                    if (window.verificationsTable) window.verificationsTable.ajax.reload();
                    Swal.fire({ text: "Pembayaran berhasil ditolak.", icon: "success", buttonsStyling: false, confirmButtonText: "Ok", customClass: { confirmButton: "btn btn-primary" } });
                } else {
                    res.json().then(function (data) {
                        Swal.fire({ text: data.message || data.errors?.rejection_reason?.[0] || "Terjadi kesalahan.", icon: "error", buttonsStyling: false, confirmButtonText: "Ok", customClass: { confirmButton: "btn btn-danger" } });
                    }).catch(function () {
                        Swal.fire({ text: "Terjadi kesalahan.", icon: "error", buttonsStyling: false, confirmButtonText: "Ok", customClass: { confirmButton: "btn btn-danger" } });
                    });
                }
            })
            .catch(function () {
                submitBtn.disabled = false;
                spinner.classList.add('d-none');
            });
        });
    });

})();
</script>
@endpush
