@extends('layouts.app')

@section('title', 'Paket Internet — K2-Net')

@section('toolbar')
    <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 my-0">Paket Internet</h1>
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Home</a>
        </li>
        <li class="breadcrumb-item">
            <span class="text-muted">/</span>
        </li>
        <li class="breadcrumb-item text-gray-900 fw-bold">Paket Internet</li>
    </ul>
@endsection

@section('toolbar_actions')
    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_create_package">
        <i class="ki-duotone ki-plus fs-2"><span class="path1"></span><span class="path2"></span></i>
        Tambah Paket
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
                           data-kt-packages-table-filter="search"
                           class="form-control form-control-solid w-200px w-sm-250px ps-12 pe-5"
                           placeholder="Cari paket..." />
                </div>
            </div>
        </div>
        <div class="card-body pt-0">
            <div class="table-responsive">
                <table id="kt_packages_table"
                       class="table align-middle table-row-dashed table-row-gray-300 fs-6 gy-5">
                    <thead>
                        <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                            <th class="w-10px">#</th>
                            <th class="min-w-150px">Nama Paket</th>
                            <th class="min-w-100px">Kecepatan</th>
                            <th class="min-w-100px text-end">Harga/Bulan</th>
                            <th class="min-w-80px text-center">Pelanggan</th>
                            <th class="min-w-80px">Status</th>
                            <th class="text-end min-w-70px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-700"></tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center flex-wrap mt-5 gap-3">
                <div id="kt_packages_length"></div>
                <div id="kt_packages_paginate"></div>
            </div>
        </div>
    </div>

    {{-- MODALS --}}

    {{-- Create Package Modal --}}
    <div class="modal fade" tabindex="-1" id="kt_modal_create_package">
        <div class="modal-dialog modal-fullscreen-sm-down">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Tambah Paket Internet</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('admin.api.packages.store') }}" id="create_package_form">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Paket <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="create_package_name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" required maxlength="255" placeholder="cth. Bronze 10 Mbps" />
                            <div class="invalid-feedback" id="create_package_name_error"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Kecepatan <span class="text-danger">*</span></label>
                            <input type="text" name="speed" id="create_package_speed"
                                   class="form-control @error('speed') is-invalid @enderror"
                                   value="{{ old('speed') }}" required maxlength="100" placeholder="cth. 10 Mbps" />
                            <div class="invalid-feedback" id="create_package_speed_error"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Harga/Bulan <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="price" id="create_package_price"
                                       class="form-control @error('price') is-invalid @enderror"
                                       value="{{ old('price') }}" required min="0" step="1000" placeholder="150000" />
                            </div>
                            <div class="invalid-feedback" id="create_package_price_error"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Deskripsi</label>
                            <textarea name="description" id="create_package_description"
                                      class="form-control" rows="2" maxlength="1000"
                                      placeholder="Deskripsi paket opsional">{{ old('description') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" name="is_active"
                                       id="create_package_active" value="1"
                                       {{ old('is_active', '1') ? 'checked' : '' }} />
                                <label class="form-check-label fw-bold" for="create_package_active">Paket Aktif</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-end">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary" id="create_package_submit">
                                <span class="spinner-border spinner-border-sm align-middle me-2 d-none" id="create_package_spinner"></span>
                                Simpan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Package Modal --}}
    <div class="modal fade" tabindex="-1" id="kt_modal_edit_package">
        <div class="modal-dialog modal-fullscreen-sm-down">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Edit Paket Internet</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="" id="edit_package_form">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Paket <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="edit_package_name"
                                   class="form-control" required maxlength="255" />
                            <div class="invalid-feedback" id="edit_package_name_error"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Kecepatan <span class="text-danger">*</span></label>
                            <input type="text" name="speed" id="edit_package_speed"
                                   class="form-control" required maxlength="100" />
                            <div class="invalid-feedback" id="edit_package_speed_error"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Harga/Bulan <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="price" id="edit_package_price"
                                       class="form-control" required min="0" step="1000" />
                            </div>
                            <div class="invalid-feedback" id="edit_package_price_error"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Deskripsi</label>
                            <textarea name="description" id="edit_package_description"
                                      class="form-control" rows="2" maxlength="1000"></textarea>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" name="is_active"
                                       id="edit_package_active" value="1" />
                                <label class="form-check-label fw-bold" for="edit_package_active">Paket Aktif</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-end">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary" id="edit_package_submit">
                                <span class="spinner-border spinner-border-sm align-middle me-2 d-none" id="edit_package_spinner"></span>
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

    var renderSpeed = function (speed) {
        return speed || '<span class="text-muted">—</span>';
    };

    var renderPrice = function (price, row) {
        return row.formatted_price || price;
    };

    var renderCustomerCount = function (count) {
        return '<span class="badge badge-light-primary">' + (count || 0) + '</span>';
    };

    var renderStatus = function (isActive) {
        var cls = isActive ? 'success' : 'danger';
        var label = isActive ? 'Aktif' : 'Nonaktif';
        return '<span class="badge badge-light-' + cls + '">' + label + '</span>';
    };

    var renderActions = function (row) {
        if (!row || !row.actions) return '';
        var editUrl   = row.actions.edit_url   || '';
        var deleteUrl = row.actions.delete_url || '';
        var html = '';
        html += '<button type="button" class="btn btn-sm btn-icon btn-light btn-active-light-primary me-1"';
        html += '        data-bs-toggle="tooltip" data-bs-placement="top"';
        html += '        title="Edit Paket"';
        html += '        onclick="openEditPackageModal(\'' + editUrl.replace(/'/g, "\\'") + '\')">';
        html += '    <i class="ki-duotone ki-pencil fs-2"><span class="path1"></span><span class="path2"></span></i>';
        html += '</button>';
        html += '<button type="button" class="btn btn-sm btn-icon btn-light btn-active-light-danger"';
        html += '        data-bs-toggle="tooltip" data-bs-placement="top"';
        html += '        title="Hapus Paket"';
        html += '        onclick="confirmDeletePackage(\'' + deleteUrl.replace(/'/g, "\\'") + '\')">';
        html += '    <i class="ki-duotone ki-trash fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>';
        html += '</button>';
        return html;
    };

    var clearEditErrors = function () {
        ['name', 'speed', 'price'].forEach(function (field) {
            var errEl = document.getElementById('edit_package_' + field + '_error');
            var inpEl = document.getElementById('edit_package_' + field);
            if (errEl) errEl.textContent = '';
            if (inpEl) inpEl.classList.remove('is-invalid');
        });
    };

    var drawCallback = function (settings) {
        var wrapper = document.querySelector('#kt_packages_table_wrapper');
        if (!wrapper) return;
        var pageEl   = wrapper.querySelector('.dataTables_paginate');
        var lengthEl = wrapper.querySelector('.dataTables_length');
        if (lengthEl) {
            var selectEl = lengthEl.querySelector('select');
            if (selectEl) selectEl.className = 'form-select form-select-sm form-select-solid w-auto';
            var wrap = document.getElementById('kt_packages_length');
            if (wrap) { wrap.innerHTML = lengthEl.outerHTML; lengthEl.remove(); }
        }
        if (pageEl) {
            var wrap = document.getElementById('kt_packages_paginate');
            if (wrap) { wrap.innerHTML = pageEl.outerHTML; pageEl.remove(); }
        }
        var tooltips = document.querySelectorAll('#kt_packages_table [data-bs-toggle="tooltip"]');
        tooltips.forEach(function (el) {
            if (!el._tooltip) { new bootstrap.Tooltip(el); }
        });
    };

    window.openEditPackageModal = function (apiUrl) {
        clearEditErrors();
        var form = document.getElementById('edit_package_form');
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
            document.getElementById('edit_package_name').value = data.name || '';
            document.getElementById('edit_package_speed').value = data.speed || '';
            document.getElementById('edit_package_price').value = data.price || '';
            document.getElementById('edit_package_description').value = data.description || '';
            document.getElementById('edit_package_active').checked = !!data.is_active;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('kt_modal_edit_package')).show();
        })
        .catch(function (err) {
            console.error('Gagal mengambil data paket:', err);
        });
    };

    window.confirmDeletePackage = function (deleteUrl) {
        Swal.fire({
            text: "Yakin ingin menghapus paket ini?",
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
                    if (window.packagesTable) window.packagesTable.ajax.reload();
                    Swal.fire({ text: "Paket berhasil dihapus.", icon: "success", buttonsStyling: false, confirmButtonText: "Ok", customClass: { confirmButton: "btn btn-primary" } });
                } else {
                    res.json().then(function (data) {
                        Swal.fire({ text: data.message || "Gagal menghapus paket.", icon: "error", buttonsStyling: false, confirmButtonText: "Ok", customClass: { confirmButton: "btn btn-danger" } });
                    }).catch(function () {
                        Swal.fire({ text: "Terjadi kesalahan saat menghapus paket.", icon: "error", buttonsStyling: false, confirmButtonText: "Ok", customClass: { confirmButton: "btn btn-danger" } });
                    });
                }
            });
        });
    };

    var initDataTable = function () {
        if (typeof jQuery === 'undefined' || !jQuery.fn.DataTable) return;
        var filterSearch = document.querySelector('[data-kt-packages-table-filter="search"]');
        var dt = jQuery('#kt_packages_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.api.packages.datatable') }}",
                type: 'GET',
                data: function (d) {}
            },
            columns: [
                { data: null,             name: null,        orderable: false, searchable: false },
                { data: 'name',           name: 'name',     orderable: true,  searchable: false },
                { data: 'speed',          name: 'speed',    orderable: true,  searchable: false },
                { data: 'formatted_price', name: 'price',    orderable: false, searchable: false, className: 'text-end' },
                { data: 'customer_count', name: null,       orderable: false, searchable: false, className: 'text-center' },
                { data: 'is_active',      name: 'is_active', orderable: true, searchable: false },
                { data: null,             name: null,        orderable: false, searchable: false, className: 'text-end' }
            ],
            columnDefs: [
                { targets: 0, render: renderRowNumber },
                { targets: 1, render: function (data) { return renderName(data); } },
                { targets: 2, render: function (data) { return renderSpeed(data); } },
                { targets: 3, render: function (data, type, row) { return renderPrice(data, row); } },
                { targets: 4, render: function (data) { return renderCustomerCount(data); } },
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
                info: 'Menampilkan _START_–_END_ dari _TOTAL_ paket',
                infoEmpty: 'Menampilkan 0 dari 0 paket',
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
        window.packagesTable = dt;
        if (filterSearch) {
            filterSearch.addEventListener('keyup', function (e) { dt.search(e.target.value).draw(); });
        }

        // Create form
        var createForm = document.getElementById('create_package_form');
        if (createForm) {
            createForm.addEventListener('submit', function (e) {
                e.preventDefault();
                var submitBtn = document.getElementById('create_package_submit');
                var spinner   = document.getElementById('create_package_spinner');
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
                        var modal = bootstrap.Modal.getInstance(document.getElementById('kt_modal_create_package'));
                        if (modal) { document.body.focus(); modal.hide(); }
                        createForm.reset();
                        document.getElementById('create_package_active').checked = true;
                        if (window.packagesTable) window.packagesTable.ajax.reload();
                        Swal.fire({ text: "Paket berhasil ditambahkan.", icon: "success", buttonsStyling: false, confirmButtonText: "Ok", customClass: { confirmButton: "btn btn-primary" } });
                    } else {
                        res.json().then(function (data) {
                            Swal.fire({ text: data.message || "Gagal menyimpan paket.", icon: "error", buttonsStyling: false, confirmButtonText: "Ok", customClass: { confirmButton: "btn btn-danger" } });
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
        var editForm = document.getElementById('edit_package_form');
        if (editForm) {
            editForm.addEventListener('submit', function (e) {
                e.preventDefault();
                var submitBtn = document.getElementById('edit_package_submit');
                var spinner   = document.getElementById('edit_package_spinner');
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
                        var modal = bootstrap.Modal.getInstance(document.getElementById('kt_modal_edit_package'));
                        if (modal) { document.body.focus(); modal.hide(); }
                        if (window.packagesTable) window.packagesTable.ajax.reload();
                        Swal.fire({ text: "Paket berhasil diperbarui.", icon: "success", buttonsStyling: false, confirmButtonText: "Ok", customClass: { confirmButton: "btn btn-primary" } });
                    } else {
                        res.json().then(function (data) {
                            Swal.fire({ text: data.message || "Gagal memperbarui paket.", icon: "error", buttonsStyling: false, confirmButtonText: "Ok", customClass: { confirmButton: "btn btn-danger" } });
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
