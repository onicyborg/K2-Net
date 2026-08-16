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

<div class="alert alert-info d-flex align-items-center p-3 mb-5">
    <i class="ki-duotone ki-information-4 fs-2 me-3 text-info"><span class="path1"></span><span class="path2"></span></i>
    <div>
        Setiap submission berisi <strong>1 atau beberapa tagihan</strong> yang dibayar sekaligus oleh pelanggan.
        Verifikasi approve/reject berlaku untuk <strong>seluruh submission</strong>.
    </div>
</div>

<div class="card card-flush">
    <div class="card-header pt-5 pb-3">
        <div class="card-title w-100 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center position-relative my-1">
                <i class="ki-duotone ki-magnifier fs-1 position-absolute text-muted" style="z-index:1; left: 12px; top: 50%; transform: translateY(-50%); pointer-events: none;"></i>
                <input type="text" data-kt-verifications-table-filter="search"
                       class="form-control form-control-solid w-200px w-sm-250px ps-12"
                       placeholder="Cari pelanggan..." />
            </div>
        </div>
    </div>
    <div class="card-body pt-0">
        <div class="table-responsive">
            <table id="kt_verifications_table" class="table align-middle table-row-dashed table-row-gray-300 fs-6 gy-5">
                <thead>
                    <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                        <th class="w-10px">#</th>
                        <th class="min-w-150px">Pelanggan</th>
                        <th class="min-w-120px">Periode Tagihan</th>
                        <th class="min-w-80px text-center">Jumlah</th>
                        <th class="min-w-120px">Transfer ke</th>
                        <th class="min-w-120px">Tanggal Transfer</th>
                        <th class="min-w-100px">Tanggal Submit</th>
                        <th class="text-end min-w-100px">Aksi</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-700"></tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center flex-wrap mt-5 gap-3">
            <div id="kt_verifications_length"></div>
            <div id="kt_verifications_paginate"></div>
        </div>
    </div>
</div>

{{-- View Detail Modal --}}
<div class="modal fade" tabindex="-1" id="kt_modal_view_verification">
    <div class="modal-dialog modal-xl modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Detail Submission</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                {{-- Submission Info --}}
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <div class="bg-light-primary rounded-3 p-3">
                            <div class="text-muted small text-uppercase fw-semibold mb-1">Pelanggan</div>
                            <div class="fw-bold text-gray-900" id="view_customer_name">—</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="bg-light-primary rounded-3 p-3">
                            <div class="text-muted small text-uppercase fw-semibold mb-1">Transfer dari</div>
                            <div class="fw-bold text-gray-900" id="view_transfer_from">—</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="bg-light-success rounded-3 p-3">
                            <div class="text-muted small text-uppercase fw-semibold mb-1">Jumlah Transfer</div>
                            <div class="fw-bold text-gray-900 fs-5" id="view_transfer_amount">—</div>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="bg-light-primary rounded-3 p-3">
                            <div class="text-muted small text-uppercase fw-semibold mb-1">Transfer ke</div>
                            <div class="fw-bold text-gray-900" id="view_transfer_to">—</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="bg-light-primary rounded-3 p-3">
                            <div class="text-muted small text-uppercase fw-semibold mb-1">Tanggal Transfer</div>
                            <div class="fw-bold text-gray-900" id="view_transfer_date">—</div>
                        </div>
                    </div>
                </div>

                {{-- Invoice List Table --}}
                <h5 class="text-gray-900 fw-bold mb-3">
                    <i class="ki-duotone ki-document fs-2 me-2"><span class="path1"></span><span class="path2"></span></i>
                    Daftar Tagihan (<span id="view_invoice_count">0</span>)
                </h5>
                <div class="table-responsive mb-4">
                    <table class="table table-sm table-row-dashed table-hover align-middle">
                        <thead class="table-light">
                            <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                                <th>No. Tagihan</th>
                                <th>Periode</th>
                                <th class="text-end">Jumlah</th>
                                <th>Jatuh Tempo</th>
                            </tr>
                        </thead>
                        <tbody id="view_invoices_list"></tbody>
                    </table>
                </div>

                {{-- Payment Proof --}}
                <h5 class="text-gray-900 fw-bold mb-3">
                    <i class="ki-duotone ki-picture fs-2 me-2"><span class="path1"></span><span class="path2"></span></i>
                    Bukti Pembayaran
                </h5>
                <div id="view_proof_container" class="mb-3"></div>
            </div>
            <div class="modal-footer d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-danger text-white" id="btn_reject_verification">
                    Tolak
                </button>
                <button type="button" class="btn btn-success" id="btn_approve_verification">
                    Setujui
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Reject Modal --}}
<div class="modal fade" tabindex="-1" id="kt_modal_reject_verification">
    <div class="modal-dialog modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title text-danger">Tolak Pembayaran</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="reject_verification_form">
                <div class="modal-body">
                    <div class="alert alert-warning d-flex align-items-center p-3 mb-3">
                        <i class="ki-duotone ki-information-4 fs-2 me-2"><span class="path1"></span><span class="path2"></span></i>
                        <small>Semua tagihan dalam submission ini akan dikembalikan ke status <strong>Belum Bayar</strong>.</small>
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

    var currentSubmissionId = null;
    var currentApproveUrl = null;
    var currentRejectUrl = null;

    var formatDate = function (dateStr) {
        if (!dateStr) return '—';
        var d = new Date(dateStr);
        return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
    };

    var formatRupiah = function (num) {
        return 'Rp' + Number(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    };

    var renderRowNumber = function (data, type, row, meta) {
        return '<span class="text-muted">' + (meta.row + meta.settings._iDisplayStart + 1) + '</span>';
    };

    var renderCustomer = function (name) {
        return name ? '<span class="fw-bold text-gray-900">' + name + '</span>' : '<span class="text-muted">—</span>';
    };

    var renderPeriods = function (periods) {
        return '<span class="text-gray-600">' + (periods || '—') + '</span>';
    };

    var renderAmount = function (amount, formatted) {
        return '<span class="fw-bold text-gray-900">' + (formatted || formatRupiah(amount)) + '</span>';
    };

    var renderBank = function (row) {
        var bank = row.bank || '';
        var num = row.account_number || '';
        var name = row.account_name || '';
        if (!bank && !num) return '<span class="text-muted">—</span>';
        return '<span class="text-gray-700">' + bank + ' — ' + num + '<br><small class="text-muted">a.n ' + name + '</small></span>';
    };

    var renderSubmittedAt = function (dt) {
        return '<span class="text-gray-600">' + (dt || '—') + '</span>';
    };

    var renderActions = function (row) {
        if (!row || !row.actions) return '';
        var showUrl = row.actions.show_url || '';
        var html = '<button type="button" class="btn btn-sm btn-light-primary me-1"';
        html += ' data-bs-toggle="tooltip" data-bs-placement="top" title="Lihat & Verifikasi"';
        html += ' onclick="openVerificationModal(\'' + showUrl.replace(/'/g, "\\'") + '\')">';
        html += '<i class="ki-duotone ki-eye fs-2 me-1"><span class="path1"></span><span class="path2"></span></i>';
        html += 'Verifikasi</button>';
        return html;
    };

    var drawCallback = function (settings) {
        var wrapper = document.querySelector('#kt_verifications_table_wrapper');
        if (!wrapper) return;
        var pageEl   = wrapper.querySelector('.dataTables_paginate');
        var lengthEl = wrapper.querySelector('.dataTables_length');
        if (lengthEl) {
            var selectEl = lengthEl.querySelector('select');
            if (selectEl) selectEl.className = 'form-select form-select-sm form-select-solid w-auto';
            var wrap = document.getElementById('kt_verifications_length');
            if (wrap) { wrap.innerHTML = lengthEl.outerHTML; lengthEl.remove(); }
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
                { data: null,             name: null,             orderable: false, searchable: false },
                { data: 'customer_name',  name: 'customer_name', orderable: true,  searchable: true },
                { data: 'billing_periods', name: null,         orderable: false, searchable: false },
                { data: 'formatted_amount', name: 'transfer_amount', orderable: true, searchable: false, className: 'text-center' },
                { data: 'bank',           name: null,           orderable: false, searchable: false },
                { data: 'account_number', name: null,         orderable: false, searchable: false },
                { data: 'account_name',   name: null,        orderable: false, searchable: false },
                { data: 'transfer_date',  name: 'transfer_date', orderable: true,  searchable: false },
                { data: 'submitted_at',    name: 'submitted_at',  orderable: true,  searchable: false },
                { data: null,             name: null,           orderable: false, searchable: false, className: 'text-end' }
            ],
            columnDefs: [
                { targets: 0, render: renderRowNumber },
                { targets: 1, render: function (d) { return renderCustomer(d); } },
                { targets: 2, render: function (d) { return renderPeriods(d); } },
                { targets: 3, render: function (d, t, row) { return renderAmount(d, row.formatted_amount); } },
                { targets: 4, render: function (d, type, row) { return renderBank(row); } },
                { targets: 5, data: 'account_number', visible: false },
                { targets: 6, data: 'account_name', visible: false },
                { targets: 7, render: function (d) { return '<span class="text-gray-600">' + formatDate(d) + '</span>'; } },
                { targets: 8, render: function (d) { return renderSubmittedAt(d); } },
                { targets: 9, render: function (d, t, row) { return renderActions(row); } }
            ],
            drawCallback: drawCallback,
            order: [[8, 'desc']],
            searchDelay: 400,
            pagingType: 'simple_numbers',
            pageLength: 10,
            lengthChange: true,
            lengthMenu: [5, 10, 20, 50],
            language: {
                processing: '<span class="spinner-border spinner-border-sm align-middle text-primary me-2"></span> Memuat...',
                lengthMenu: 'Tampilkan _MENU_',
                zeroRecords: 'Tidak ada submission yang menunggu verifikasi',
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
            currentSubmissionId = data.id;
            currentApproveUrl  = data.actions?.approve_url || apiUrl.replace('/show', '/approve');
            currentRejectUrl   = data.actions?.reject_url  || apiUrl.replace('/show', '/reject');

            document.getElementById('view_customer_name').textContent  = data.customer?.name || '—';
            document.getElementById('view_transfer_from').textContent = data.transfer_from || '—';
            document.getElementById('view_transfer_amount').textContent = data.formatted_amount || '—';
            document.getElementById('view_transfer_to').textContent   = (data.bank || '—') + ' — ' + (data.account_number || '') + ' a.n ' + (data.account_name || '');
            document.getElementById('view_transfer_date').textContent = formatDate(data.transfer_date);

            // Invoice list
            var invoices = data.invoices || [];
            document.getElementById('view_invoice_count').textContent = invoices.length;
            var listBody = document.getElementById('view_invoices_list');
            listBody.innerHTML = '';
            if (invoices.length === 0) {
                listBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">Tidak ada data tagihan.</td></tr>';
            } else {
                invoices.forEach(function (inv) {
                    var bp = inv.billing_period || '';
                    var period = new Date(bp).toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
                    var tr = document.createElement('tr');
                    tr.innerHTML =
                        '<td><span class="text-primary fw-bold">' + (inv.invoice_number || '—') + '</span></td>' +
                        '<td><span class="text-gray-700">' + period + '</span></td>' +
                        '<td class="text-end"><span class="fw-bold text-gray-900">' + formatRupiah(inv.amount) + '</span></td>' +
                        '<td><span class="text-gray-600">' + formatDate(inv.due_date) + '</span></td>';
                    listBody.appendChild(tr);
                });
                // Total row
                var totalTr = document.createElement('tr');
                totalTr.className = 'table-secondary fw-bold';
                totalTr.innerHTML =
                    '<td colspan="2">Total</td>' +
                    '<td class="text-end"><span class="text-gray-900">' + data.formatted_amount + '</span></td>' +
                    '<td></td>';
                listBody.appendChild(totalTr);
            }

            // Payment proof
            var proofContainer = document.getElementById('view_proof_container');
            if (data.payment_proof) {
                var proof = data.payment_proof;
                var proofUrl = proof.file_url || ('/storage/' + proof.file_path);
                if (['jpg', 'jpeg', 'png'].includes(proof.file_type?.toLowerCase())) {
                    proofContainer.innerHTML =
                        '<div class="d-flex flex-column align-items-center gap-3">' +
                        '<div class="border rounded-3 overflow-hidden" style="max-width: 400px;">' +
                        '<img src="' + proofUrl + '" alt="Bukti Transfer" class="img-fluid d-block mx-auto" style="max-height: 300px;" />' +
                        '<div class="p-2 text-muted small text-center">' + proof.file_name + '</div>' +
                        '</div>' +
                        '<a href="' + proofUrl + '" target="_blank" class="btn btn-sm btn-primary"><i class="ki-duotone ki-external fs-2 me-1"><span></span></i>Buka di Tab Baru</a>' +
                        '</div>';
                } else {
                    proofContainer.innerHTML =
                        '<div class="d-flex align-items-center gap-3 p-3 border rounded-3">' +
                        '<i class="ki-duotone ki-deliver fs-2x text-primary"><span></span></i>' +
                        '<div><div class="fw-bold text-gray-900">' + proof.file_name + '</div>' +
                        '<small class="text-muted">PDF — klik untuk download</small></div>' +
                        '<a href="' + proofUrl + '" target="_blank" class="btn btn-sm btn-primary ms-auto">Download</a>' +
                        '</div>';
                }
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
                text: "Setujui submission ini? Semua tagihan di dalamnya akan ditandai lunas.",
                icon: "success",
                buttonsStyling: false,
                showCancelButton: true,
                confirmButtonText: "Ya, Setujui",
                cancelButtonText: "Batal",
                customClass: { confirmButton: "btn btn-success", cancelButton: "btn btn-light" }
            }).then(function (result) {
                if (!result.isConfirmed) return;
                handleVerificationAction(currentApproveUrl, 'POST', {}, "Submission berhasil disetujui. Semua tagihan ditandai lunas.");
            });
        });

        // Reject button
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
                    Swal.fire({ text: "Submission berhasil ditolak.", icon: "success", buttonsStyling: false, confirmButtonText: "Ok", customClass: { confirmButton: "btn btn-primary" } });
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
