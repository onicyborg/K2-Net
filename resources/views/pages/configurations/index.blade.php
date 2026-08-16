@extends('layouts.app')

@section('title', 'Konfigurasi Sistem — K2-Net')

@push('extra_styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@yaireo/tagify@4.17.9/dist/tagify.css" />
<style>
    .tagify-container:focus-within { border-color: #0091ea !important; box-shadow: 0 0 0 0.2rem rgba(0,145,234,.15) !important; }
</style>
@endpush

@section('toolbar')
    <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 my-0">Konfigurasi Sistem</h1>
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Home</a>
        </li>
        <li class="breadcrumb-item"><span class="text-muted">/</span></li>
        <li class="breadcrumb-item text-gray-900 fw-bold">Konfigurasi Sistem</li>
    </ul>
@endsection

@section('content')

<form id="config_form">
    @csrf

    {{-- CARD 1: Informasi Perusahaan --}}
    <div class="card card-flush mb-5">
        <div class="card-header">
            <div class="card-title">
                <h3 class="fw-bold fs-4 text-gray-900">
                    <i class="ki-duotone ki-office fs-2 me-2 text-primary"><span></span></i>
                    Informasi Perusahaan
                </h3>
            </div>
        </div>
        <div class="card-body">
            <div class="row gx-5">
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-gray-700">Nama Perusahaan</label>
                    <input type="text" class="form-control form-control-solid" id="cfg_company_name" />
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-gray-700">Nomor Telepon</label>
                    <input type="text" class="form-control form-control-solid" id="cfg_company_phone" placeholder="08xxxxxxxxxx" />
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-gray-700">Alamat</label>
                    <input type="text" class="form-control form-control-solid" id="cfg_company_address" placeholder="Jl. Nama Jalan No. X" />
                </div>
            </div>
        </div>
    </div>

    {{-- CARD 2: Pembayaran & Tagihan --}}
    <div class="card card-flush mb-5">
        <div class="card-header">
            <div class="card-title">
                <h3 class="fw-bold fs-4 text-gray-900">
                    <i class="ki-duotone ki-wallet fs-2 me-2 text-primary"><span></span></i>
                    Pembayaran & Tagihan
                </h3>
            </div>
        </div>
        <div class="card-body">
            <div class="row gx-5 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-gray-700">Tanggal Jatuh Tempo</label>
                    <div class="input-group input-group-solid">
                        <input type="number" class="form-control form-control-solid" id="cfg_invoice_due_day" min="1" max="28" placeholder="15" />
                        <span class="input-group-text bg-light text-muted">/bulan berikutnya</span>
                    </div>
                    <div class="form-text text-muted small">Format: tanggal X di bulan berikutnya (1–28)</div>
                </div>
            </div>

            <div class="separator separator-dashed my-5"></div>

            <div class="mb-1">
                <label class="form-label fw-semibold text-gray-700">Informasi Rekening Bank</label>
                <div class="text-muted small mb-3">Rekening tujuan transfer untuk pelanggan.</div>
            </div>
            <div class="row gx-5 align-items-end">
                <div class="col-md-4">
                    <label class="form-label text-gray-600 small">Nama Bank</label>
                    <input type="text" class="form-control form-control-solid" id="cfg_bank_name" placeholder="Bank BCA" />
                </div>
                <div class="col-md-4">
                    <label class="form-label text-gray-600 small">Nomor Rekening</label>
                    <input type="text" class="form-control form-control-solid" id="cfg_bank_account_number" placeholder="1234567890" />
                </div>
                <div class="col-md-4">
                    <label class="form-label text-gray-600 small">Atas Nama</label>
                    <input type="text" class="form-control form-control-solid" id="cfg_bank_account_name" placeholder="Nama Pemilik Rekening" />
                </div>
            </div>
        </div>
    </div>

    {{-- CARD 3: Sistem --}}
    <div class="card card-flush mb-5">
        <div class="card-header">
            <div class="card-title">
                <h3 class="fw-bold fs-4 text-gray-900">
                    <i class="ki-duotone ki-gear fs-2 me-2 text-primary"><span></span></i>
                    Pengaturan Sistem
                </h3>
            </div>
        </div>
        <div class="card-body">
            <div class="row gx-5">
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-gray-700">Maks Ukuran Upload Bukti Transfer</label>
                    <div class="input-group input-group-solid">
                        <input type="number" class="form-control form-control-solid" id="cfg_upload_max_size_kb" min="512" max="51200" placeholder="5120" />
                        <span class="input-group-text bg-light text-muted">KB</span>
                    </div>
                    <div class="form-text text-muted small">Batas maksimal ukuran file upload (512 KB – 50 MB)</div>
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-semibold text-gray-700">Tipe File yang Dizinkan</label>
                    <input type="text" class="form-control" id="cfg_upload_allowed_types" placeholder="pdf, jpg, png" />
                    <div class="form-text text-muted small">Tekan Enter atau koma untuk menambah tipe file</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sticky Action Bar --}}
    <div class="card card-flush mb-5" id="action_bar" style="display:none;">
        <div class="card-body d-flex justify-content-end align-items-center gap-3 py-4">
            <div id="save_spinner" class="text-muted" style="display:none;">
                <span class="spinner-border spinner-border-sm align-middle text-primary me-2"></span>
                Menyimpan...
            </div>
            <button type="submit" class="btn btn-primary btn-lg" id="btn_save_all">
                <i class="ki-duotone ki-check-circle fs-2 me-2"><span></span></i>
                <span class="d-none d-sm-inline">Simpan Semua Pengaturan</span>
                <span class="d-sm-none">Simpan</span>
            </button>
        </div>
    </div>

</form>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify@4.17.9/dist/tagify.min.js"></script>
<script>
(function () {
    "use strict";

    var bankTagify = null;
    var hasChanges = false;

    var showActionBar = function () {
        if (!hasChanges) {
            hasChanges = true;
            document.getElementById('action_bar').style.display = '';
        }
    };

    var loadConfigs = function () {
        fetch("{{ route('admin.api.configurations.datatable') }}", {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            var all = Object.values(data.configs || {}).flat();

            all.forEach(function (cfg) {
                var val = cfg.value || '';
                if (cfg.key === 'company_name') {
                    document.getElementById('cfg_company_name').value = val;
                } else if (cfg.key === 'company_phone') {
                    document.getElementById('cfg_company_phone').value = val;
                } else if (cfg.key === 'company_address') {
                    document.getElementById('cfg_company_address').value = val;
                } else if (cfg.key === 'invoice_due_day') {
                    document.getElementById('cfg_invoice_due_day').value = val;
                } else if (cfg.key === 'upload_max_size_kb') {
                    document.getElementById('cfg_upload_max_size_kb').value = val;
                } else if (cfg.key === 'upload_allowed_types') {
                    try {
                        var tags = JSON.parse(val);
                        if (Array.isArray(tags)) {
                            bankTagify = new Tagify(document.getElementById('cfg_upload_allowed_types'), {
                                whitelist: ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp'],
                                enforceWhitelist: false,
                                delimiters: ',|Enter',
                                placeholder: 'Ketik tipe file, tekan Enter...',
                            });
                            if (tags.length) {
                                bankTagify.addTags(tags.map(function (t) { return { value: t }; }));
                            }
                        }
                    } catch (e) {}
                } else if (cfg.key === 'bank_account_info') {
                    try {
                        var banks = JSON.parse(val);
                        if (Array.isArray(banks) && banks.length) {
                            document.getElementById('cfg_bank_name').value = banks[0].bank || '';
                            document.getElementById('cfg_bank_account_number').value = banks[0].account_number || '';
                            document.getElementById('cfg_bank_account_name').value = banks[0].account_name || '';
                        }
                    } catch (e) {}
                }
            });
        })
        .catch(function (err) { console.error(err); });
    };

    document.querySelectorAll('#config_form input, #config_form textarea').forEach(function (el) {
        el.addEventListener('input', showActionBar);
        el.addEventListener('change', showActionBar);
    });

    document.getElementById('config_form').addEventListener('submit', function (e) {
        e.preventDefault();
        if (!hasChanges) return;

        var payload = [
            { key: 'company_name',       value: document.getElementById('cfg_company_name').value },
            { key: 'company_address',    value: document.getElementById('cfg_company_address').value },
            { key: 'company_phone',      value: document.getElementById('cfg_company_phone').value },
            { key: 'invoice_due_day',   value: document.getElementById('cfg_invoice_due_day').value },
            { key: 'upload_max_size_kb', value: document.getElementById('cfg_upload_max_size_kb').value },
            {
                key: 'upload_allowed_types',
                value: JSON.stringify(bankTagify ? bankTagify.value.map(function (t) { return t.value; }) : [])
            },
            {
                key: 'bank_account_info',
                value: JSON.stringify([{
                    bank:           document.getElementById('cfg_bank_name').value,
                    account_number: document.getElementById('cfg_bank_account_number').value,
                    account_name:   document.getElementById('cfg_bank_account_name').value,
                }])
            },
        ];

        var btn = document.getElementById('btn_save_all');
        var spinner = document.getElementById('save_spinner');
        btn.disabled = true;
        spinner.style.display = '';

        Promise.all(payload.map(function (item) {
            return fetch("{{ route('admin.api.configurations.update') }}", {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') || {content: ''}).content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(item),
            }).then(function (res) { return res.json(); });
        }))
        .then(function (results) {
            var errors = results.filter(function (r) { return r.message && r.message.includes('Gagal'); });
            if (errors.length) {
                Swal.fire({ text: 'Beberapa pengaturan gagal disimpan.', icon: 'error', buttonsStyling: false, confirmButtonText: 'Ok', customClass: { confirmButton: 'btn btn-danger' } });
            } else {
                hasChanges = false;
                Swal.fire({ text: 'Semua pengaturan berhasil disimpan.', icon: 'success', buttonsStyling: false, confirmButtonText: 'Ok', customClass: { confirmButton: 'btn btn-primary' } });
            }
        })
        .catch(function () {
            Swal.fire({ text: 'Gagal menyimpan.', icon: 'error', buttonsStyling: false, confirmButtonText: 'Ok', customClass: { confirmButton: 'btn btn-danger' } });
        })
        .finally(function () {
            btn.disabled = false;
            spinner.style.display = 'none';
        });
    });

    KTUtil.onDOMContentLoaded(function () { loadConfigs(); });
})();
</script>
@endpush
