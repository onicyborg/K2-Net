@extends('layouts.app')

@section('title', 'Pelanggan — K2-Net')

@section('toolbar')
    <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 my-0">Pelanggan</h1>
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Home</a>
        </li>
        <li class="breadcrumb-item">
            <span class="text-muted">/</span>
        </li>
        <li class="breadcrumb-item text-gray-900 fw-bold">Pelanggan</li>
    </ul>
@endsection

@section('toolbar_actions')
    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_create_customer">
        <i class="ki-duotone ki-plus fs-2"><span class="path1"></span><span class="path2"></span></i>
        Tambah Pelanggan
    </button>
@endsection

@section('content')

    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center p-3 mb-5">
            <i class="ki-duotone ki-check-circle fs-2 text-success me-2"><span class="path1"></span><span class="path2"></span></i>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger d-flex align-items-center p-3 mb-5">
            <i class="ki-duotone ki-cross-circle fs-2 text-danger me-2"><span class="path1"></span><span class="path2"></span></i>
            {{ session('error') }}
        </div>
    @endif

    <div class="card card-flush">
        <div class="card-header pt-5 pb-3">
            <div class="card-title w-100 d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-duotone ki-magnifier fs-1 position-absolute text-muted"
                       style="z-index:1; left: 12px; top: 50%; transform: translateY(-50%); pointer-events: none;"></i>
                    <input type="text"
                           data-kt-customers-table-filter="search"
                           class="form-control form-control-solid w-200px w-sm-250px ps-12 pe-5"
                           placeholder="Cari pelanggan..." />
                </div>
            </div>
        </div>
        <div class="card-body pt-0">
            <div class="table-responsive">
                <table id="kt_customers_table"
                       class="table align-middle table-row-dashed table-row-gray-300 fs-6 gy-5">
                    <thead>
                        <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                            <th class="w-10px">#</th>
                            <th class="min-w-150px">Nama</th>
                            <th class="min-w-120px">Email</th>
                            <th class="min-w-100px">No. HP</th>
                            <th class="min-w-100px">Paket</th>
                            <th class="min-w-80px">Status</th>
                            <th class="text-end min-w-70px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-700"></tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center flex-wrap mt-5 gap-3">
                <div id="kt_customers_length"></div>
                <div id="kt_customers_paginate"></div>
            </div>
        </div>
    </div>

    {{-- MODALS --}}

    {{-- Create Customer Modal --}}
    <div class="modal fade" tabindex="-1" id="kt_modal_create_customer">
        <div class="modal-dialog modal-fullscreen-sm-down">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Tambah Pelanggan</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('admin.api.customers.store') }}" id="create_customer_form">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="create_customer_name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" required maxlength="255" placeholder="Nama lengkap pelanggan" />
                            <div class="invalid-feedback" id="create_customer_name_error"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Email</label>
                            <input type="email" name="email" id="create_customer_email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" maxlength="255" placeholder="email@example.com" />
                            <div class="invalid-feedback" id="create_customer_email_error"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">No. WhatsApp</label>
                            <input type="text" name="whatsapp_number" id="create_customer_phone"
                                   class="form-control @error('whatsapp_number') is-invalid @enderror"
                                   value="{{ old('whatsapp_number') }}" maxlength="20" placeholder="08xxxxxxxxxx" />
                            <div class="invalid-feedback" id="create_customer_phone_error"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Alamat</label>
                            <textarea name="address" id="create_customer_address"
                                      class="form-control" rows="2" maxlength="500"
                                      placeholder="Alamat lengkap">{{ old('address') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Paket Internet <span class="text-danger">*</span></label>
                            <select name="package_id" id="create_customer_package"
                                    class="form-select @error('package_id') is-invalid @enderror" required>
                                <option value="">— Pilih Paket —</option>
                                @foreach($packages as $package)
                                <option value="{{ $package->id }}" {{ old('package_id') == $package->id ? 'selected' : '' }}>
                                    {{ $package->name }} — {{ $package->speed }} ({{ $package->formattedPrice() }})
                                </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="create_customer_package_error"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                            <select name="status" id="create_customer_status"
                                    class="form-select @error('status') is-invalid @enderror" required>
                                <option value="aktif" {{ old('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="isolir" {{ old('status') == 'isolir' ? 'selected' : '' }}>Isolir</option>
                                <option value="nonaktif" {{ old('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                            </select>
                            <div class="invalid-feedback" id="create_customer_status_error"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Catatan</label>
                            <textarea name="notes" id="create_customer_notes"
                                      class="form-control" rows="2" maxlength="1000"
                                      placeholder="Catatan opsional">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-end">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary" id="create_customer_submit">
                                <span class="spinner-border spinner-border-sm align-middle me-2 d-none" id="create_customer_spinner"></span>
                                Simpan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Customer Modal --}}
    <div class="modal fade" tabindex="-1" id="kt_modal_edit_customer">
        <div class="modal-dialog modal-fullscreen-sm-down">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Edit Pelanggan</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="" id="edit_customer_form">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="edit_customer_name"
                                   class="form-control" required maxlength="255" />
                            <div class="invalid-feedback" id="edit_customer_name_error"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Email</label>
                            <input type="email" name="email" id="edit_customer_email"
                                   class="form-control" maxlength="255" />
                            <div class="invalid-feedback" id="edit_customer_email_error"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">No. WhatsApp</label>
                            <input type="text" name="whatsapp_number" id="edit_customer_phone"
                                   class="form-control" maxlength="20" />
                            <div class="invalid-feedback" id="edit_customer_phone_error"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Alamat</label>
                            <textarea name="address" id="edit_customer_address"
                                      class="form-control" rows="2" maxlength="500"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Paket Internet <span class="text-danger">*</span></label>
                            <select name="package_id" id="edit_customer_package" class="form-select" required>
                                <option value="">— Pilih Paket —</option>
                                @foreach($packages as $package)
                                <option value="{{ $package->id }}">{{ $package->name }} — {{ $package->speed }} ({{ $package->formattedPrice() }})</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="edit_customer_package_error"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                            <select name="status" id="edit_customer_status" class="form-select" required>
                                <option value="aktif">Aktif</option>
                                <option value="isolir">Isolir</option>
                                <option value="nonaktif">Nonaktif</option>
                            </select>
                            <div class="invalid-feedback" id="edit_customer_status_error"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Catatan</label>
                            <textarea name="notes" id="edit_customer_notes"
                                      class="form-control" rows="2" maxlength="1000"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-end">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary" id="edit_customer_submit">
                                <span class="spinner-border spinner-border-sm align-middle me-2 d-none" id="edit_customer_spinner"></span>
                                Simpan Perubahan
                            </button>
                        </div>
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

    var renderRowNumber = function (data, type, row, meta) {
        return '<span class="text-muted">' + (meta.row + meta.settings._iDisplayStart + 1) + '</span>';
    };

    var renderName = function (name) {
        return '<span class="fw-bold text-gray-900">' + (name || '—') + '</span>';
    };

    var renderEmail = function (email) {
        return email ? '<span class="text-gray-600">' + email + '</span>' : '<span class="text-muted">—</span>';
    };

    var renderPhone = function (phone) {
        return phone || '<span class="text-muted">—</span>';
    };

    var renderPackage = function (packageName) {
        return packageName ? '<span class="badge badge-light-primary">' + packageName + '</span>' : '<span class="text-muted">—</span>';
    };

    var renderStatus = function (status) {
        var map = {
            'aktif': { cls: 'success', label: 'Aktif' },
            'isolir': { cls: 'warning', label: 'Isolir' },
            'nonaktif': { cls: 'dark', label: 'Nonaktif' }
        };
        var s = map[status] || { cls: 'secondary', label: status || '—' };
        return '<span class="badge badge-light-' + s.cls + '">' + s.label + '</span>';
    };

    var renderActions = function (row) {
        if (!row || !row.actions) return '';
        var editUrl   = row.actions.edit_url   || '';
        var deleteUrl = row.actions.delete_url || '';
        var html = '';
        html += '<button type="button" class="btn btn-sm btn-icon btn-light btn-active-light-primary me-1"';
        html += '        data-bs-toggle="tooltip" data-bs-placement="top"';
        html += '        title="Edit Pelanggan"';
        html += '        onclick="openEditCustomerModal(\'' + editUrl.replace(/'/g, "\\'") + '\')">';
        html += '    <i class="ki-duotone ki-pencil fs-2"><span class="path1"></span><span class="path2"></span></i>';
        html += '</button>';
        html += '<button type="button" class="btn btn-sm btn-icon btn-light btn-active-light-danger"';
        html += '        data-bs-toggle="tooltip" data-bs-placement="top"';
        html += '        title="Hapus Pelanggan"';
        html += '        onclick="confirmDeleteCustomer(\'' + deleteUrl.replace(/'/g, "\\'") + '\')">';
        html += '    <i class="ki-duotone ki-trash fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>';
        html += '</button>';
        return html;
    };

    var clearCreateErrors = function () {
        ['name', 'email', 'phone', 'package', 'status'].forEach(function (field) {
            var errEl = document.getElementById('create_customer_' + field + '_error');
            var inpEl = document.getElementById('create_customer_' + field);
            if (errEl) errEl.textContent = '';
            if (inpEl) inpEl.classList.remove('is-invalid');
        });
    };

    var drawCallback = function (settings) {
        var wrapper = document.querySelector('#kt_customers_table_wrapper');
        if (!wrapper) return;
        var pageEl   = wrapper.querySelector('.dataTables_paginate');
        var lengthEl = wrapper.querySelector('.dataTables_length');
        if (lengthEl) {
            var selectEl = lengthEl.querySelector('select');
            if (selectEl) selectEl.className = 'form-select form-select-sm form-select-solid w-auto';
            var wrap = document.getElementById('kt_customers_length');
            if (wrap) { wrap.innerHTML = lengthEl.outerHTML; lengthEl.remove(); }
        }
        if (pageEl) {
            var wrap = document.getElementById('kt_customers_paginate');
            if (wrap) { wrap.innerHTML = pageEl.outerHTML; pageEl.remove(); }
        }
        var tooltips = document.querySelectorAll('#kt_customers_table [data-bs-toggle="tooltip"]');
        tooltips.forEach(function (el) {
            if (!el._tooltip) { new bootstrap.Tooltip(el); }
        });
    };

    window.openEditCustomerModal = function (apiUrl) {
        clearCreateErrors();
        var form = document.getElementById('edit_customer_form');
        form.action = apiUrl;
        var csrfEl = document.querySelector('meta[name="csrf-token"]');
        if (csrfEl) {
            var inp = form.querySelector('[name="_token"]');
            if (!inp) {
                inp = document.createElement('input');
                inp.type = 'hidden'; inp.name = '_token';
                form.insertBefore(inp, form.firstChild);
            }
            inp.value = csrfEl.content;
        }
        fetch(apiUrl, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            document.getElementById('edit_customer_name').value = data.name || '';
            document.getElementById('edit_customer_email').value = data.email || '';
            document.getElementById('edit_customer_phone').value = data.whatsapp_number || '';
            document.getElementById('edit_customer_address').value = data.address || '';
            document.getElementById('edit_customer_package').value = data.package_id || '';
            document.getElementById('edit_customer_status').value = data.status || 'aktif';
            document.getElementById('edit_customer_notes').value = data.notes || '';
            bootstrap.Modal.getOrCreateInstance(document.getElementById('kt_modal_edit_customer')).show();
        })
        .catch(function (err) {
            console.error('Gagal mengambil data pelanggan:', err);
        });
    };

    window.confirmDeleteCustomer = function (deleteUrl) {
        Swal.fire({
            text: "Yakin ingin menghapus pelanggan ini?",
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
                    if (window.customersTable) window.customersTable.ajax.reload();
                    Swal.fire({ text: "Pelanggan berhasil dihapus.", icon: "success", buttonsStyling: false, confirmButtonText: "Ok", customClass: { confirmButton: "btn btn-primary" } });
                } else {
                    res.json().then(function (data) {
                        Swal.fire({ text: data.message || "Gagal menghapus pelanggan.", icon: "error", buttonsStyling: false, confirmButtonText: "Ok", customClass: { confirmButton: "btn btn-danger" } });
                    }).catch(function () {
                        Swal.fire({ text: "Terjadi kesalahan saat menghapus pelanggan.", icon: "error", buttonsStyling: false, confirmButtonText: "Ok", customClass: { confirmButton: "btn btn-danger" } });
                    });
                }
            });
        });
    };

    var initDataTable = function () {
        if (typeof jQuery === 'undefined' || !jQuery.fn.DataTable) return;
        var filterSearch = document.querySelector('[data-kt-customers-table-filter="search"]');
        var dt = jQuery('#kt_customers_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.api.customers.datatable') }}",
                type: 'GET',
                data: function (d) {}
            },
            columns: [
                { data: null,           name: null,        orderable: false, searchable: false },
                { data: 'name',         name: 'name',     orderable: true,  searchable: false },
                { data: 'email',        name: 'email',    orderable: false, searchable: false },
                { data: 'phone',        name: 'phone',    orderable: false, searchable: false },
                { data: 'package_name', name: 'package',  orderable: false, searchable: false },
                { data: 'status',       name: 'status',   orderable: true,  searchable: false },
                { data: null,           name: null,        orderable: false, searchable: false, className: 'text-end' }
            ],
            columnDefs: [
                { targets: 0, render: renderRowNumber },
                { targets: 1, render: function (data) { return renderName(data); } },
                { targets: 2, render: function (data) { return renderEmail(data); } },
                { targets: 3, render: function (data) { return renderPhone(data); } },
                { targets: 4, render: function (data) { return renderPackage(data); } },
                { targets: 5, render: function (data) { return renderStatus(data); } },
                { targets: 6, render: function (data, type, row) { return renderActions(row); } }
            ],
            drawCallback: drawCallback,
            order: [[1, 'asc']],
            searchDelay: 400,
            pagingType: 'simple_numbers',
            pageLength: 10,
            lengthChange: true,
            lengthMenu: [5, 10, 20, 50, 100],
            language: {
                processing: '<span class="spinner-border spinner-border-sm align-middle text-primary me-2"></span> Memuat...',
                lengthMenu: 'Tampilkan _MENU_',
                zeroRecords: 'Tidak ada data yang cocok',
                info: 'Menampilkan _START_–_END_ dari _TOTAL_ pelanggan',
                infoEmpty: 'Menampilkan 0 dari 0 pelanggan',
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
        window.customersTable = dt;
        if (filterSearch) {
            filterSearch.addEventListener('keyup', function (e) { dt.search(e.target.value).draw(); });
        }

        // Create form
        var createForm = document.getElementById('create_customer_form');
        if (createForm) {
            createForm.addEventListener('submit', function (e) {
                e.preventDefault();
                clearCreateErrors();
                var submitBtn = document.getElementById('create_customer_submit');
                var spinner   = document.getElementById('create_customer_spinner');
                var csrfEl    = document.querySelector('meta[name="csrf-token"]');
                submitBtn.disabled = true;
                spinner.classList.remove('d-none');
                var formData = new FormData(createForm);
                fetch(createForm.action, {
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
                        var modal = bootstrap.Modal.getInstance(document.getElementById('kt_modal_create_customer'));
                        if (modal) { document.body.focus(); modal.hide(); }
                        createForm.reset();
                        if (window.customersTable) window.customersTable.ajax.reload();
                        Swal.fire({ text: "Pelanggan berhasil ditambahkan.", icon: "success", buttonsStyling: false, confirmButtonText: "Ok", customClass: { confirmButton: "btn btn-primary" } });
                    } else {
                        res.json().then(function (data) {
                            if (data.errors) {
                                Object.keys(data.errors).forEach(function (field) {
                                    var errEl = document.getElementById('create_customer_' + field + '_error');
                                    var inpEl = document.getElementById('create_customer_' + field);
                                    if (errEl) { errEl.textContent = Array.isArray(data.errors[field]) ? data.errors[field][0] : data.errors[field]; errEl.style.display = 'block'; }
                                    if (inpEl) inpEl.classList.add('is-invalid');
                                });
                            }
                            Swal.fire({ text: data.message || "Gagal menyimpan pelanggan.", icon: "error", buttonsStyling: false, confirmButtonText: "Ok", customClass: { confirmButton: "btn btn-danger" } });
                        }).catch(function () {
                            Swal.fire({ text: "Terjadi kesalahan saat menyimpan.", icon: "error", buttonsStyling: false, confirmButtonText: "Ok", customClass: { confirmButton: "btn btn-danger" } });
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
        var editForm = document.getElementById('edit_customer_form');
        if (editForm) {
            editForm.addEventListener('submit', function (e) {
                e.preventDefault();
                var submitBtn = document.getElementById('edit_customer_submit');
                var spinner   = document.getElementById('edit_customer_spinner');
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
                        var modal = bootstrap.Modal.getInstance(document.getElementById('kt_modal_edit_customer'));
                        if (modal) { document.body.focus(); modal.hide(); }
                        if (window.customersTable) window.customersTable.ajax.reload();
                        Swal.fire({ text: "Pelanggan berhasil diperbarui.", icon: "success", buttonsStyling: false, confirmButtonText: "Ok", customClass: { confirmButton: "btn btn-primary" } });
                    } else {
                        res.json().then(function (data) {
                            Swal.fire({ text: data.message || "Gagal memperbarui pelanggan.", icon: "error", buttonsStyling: false, confirmButtonText: "Ok", customClass: { confirmButton: "btn btn-danger" } });
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
        }
    };

    KTUtil.onDOMContentLoaded(function () { initDataTable(); });

})();
</script>
@endpush
