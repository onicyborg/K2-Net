@extends('layouts.portal')

@section('title', 'Portal Pembayaran — K2-Net')

@section('content')

{{-- SUCCESS MESSAGE --}}
@if(session('success'))
    <div class="alert alert-success d-flex align-items-center p-5 mb-0" style="max-width: 700px; margin: 0 auto;">
        <i class="ki-duotone ki-check-circle fs-2 text-success me-4"><span class="path1"></span><span class="path2"></span></i>
        <div>
            <h4 class="mb-1 text-success fw-bold">Berhasil!</h4>
            <span>{{ session('success') }}</span>
        </div>
    </div>
@endif

{{-- NO INVOICES STATE --}}
@if($invoices->isEmpty())
    <div class="card card-flush" style="max-width: 700px; margin: 0 auto;">
        <div class="card-body py-15 text-center">
            <img src="https://magang.skripsian.site/assets/media/svg/illustrations/misc/under-maintenance.svg"
                 alt="" class="mb-5" style="max-height: 120px;" />
            <h3 class="text-gray-900 fw-bold mb-2">Tidak Ada Tagihan</h3>
            <p class="text-gray-500 mb-0">
                Halo <strong>{{ $customer->name }}</strong>!<br />
                Saat ini tidak ada tagihan yang perlu dibayar.
            </p>
            <p class="text-muted small mt-5 mb-0">
                Kode Akses: <code>{{ $customer->portal_code }}</code>
            </p>
        </div>
    </div>
@else

{{-- INVOICE LIST CARD --}}
<div class="card card-flush" style="max-width: 800px; margin: 0 auto;">
    <div class="card-header pt-5 pb-0">
        <div class="d-flex align-items-center gap-3">
            <div class="symbol symbol-40px symbol-circle">
                <span class="symbol-label bg-primary text-white fw-bold">
                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                </span>
            </div>
            <div>
                <h2 class="text-gray-900 fw-bold mb-0">{{ $customer->name }}</h2>
                <span class="text-muted small">{{ $customer->package?->name }} — {{ $customer->package?->speed }}</span>
            </div>
        </div>
        <div class="badge badge-light-success ms-auto">Aktif</div>
    </div>

    <div class="card-body py-5">
        <h5 class="text-gray-900 fw-bold mb-4">
            <i class="ki-duotone ki-document fs-2 me-2"><span class="path1"></span><span class="path2"></span></i>
            Tagihan Anda
        </h5>

        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-300 align-middle gs-3 gy-4">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase">
                        <th class="w-40px">
                            <div class="form-check form-check-sm form-check-custom">
                                <input class="form-check-input" type="checkbox" id="select_all" />
                            </div>
                        </th>
                        <th>No. Tagihan</th>
                        <th>Periode</th>
                        <th class="text-end">Jumlah</th>
                        <th class="text-center">Jatuh Tempo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoices as $invoice)
                    <tr class="invoice-row {{ $invoice->isOverdue() ? 'table-danger' : '' }}"
                        data-id="{{ $invoice->id }}" data-amount="{{ $invoice->amount }}">
                        <td>
                            <div class="form-check form-check-sm form-check-custom">
                                <input class="form-check-input invoice-checkbox" type="checkbox"
                                       value="{{ $invoice->id }}" />
                            </div>
                        </td>
                        <td>
                            <span class="text-primary fw-bold">{{ $invoice->invoice_number }}</span>
                        </td>
                        <td>
                            <span class="text-gray-700">{{ \Carbon\Carbon::parse($invoice->billing_period)->format('F Y') }}</span>
                        </td>
                        <td class="text-end">
                            <span class="fw-bold text-gray-900">{{ $invoice->formattedAmount() }}</span>
                        </td>
                        <td class="text-center">
                            @if($invoice->isOverdue())
                                <span class="badge badge-danger">Terlambat</span>
                            @else
                                <span class="text-muted small">{{ $invoice->due_date->format('d M Y') }}</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="separator my-5"></div>

        {{-- TOTAL + BANK INFO --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <span class="text-gray-700 fw-semibold">Total Tagihan:</span>
            <span class="fs-3 fw-bold text-primary" id="total_amount">Rp0</span>
        </div>

        {{-- PAYMENT FORM --}}
        <form id="payment_form" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="invoice_ids" id="invoice_ids" value="" />

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Transfer ke <span class="text-danger">*</span></label>
                    <select name="transfer_to" id="transfer_to" class="form-select" required>
                        <option value="">— Pilih Rekening —</option>
                        @foreach($bankAccounts as $bank)
                        <option value="{{ $bank['bank'] }}|{{ $bank['account_number'] }}|{{ $bank['account_name'] }}">
                            {{ $bank['bank'] }} — {{ $bank['account_number'] }} a.n {{ $bank['account_name'] }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Jumlah Transfer (Rp) <span class="text-danger">*</span></label>
                    <input type="number" name="transfer_amount" id="transfer_amount"
                           class="form-control" required min="1000" placeholder="Masukkan jumlah transfer" />
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Transfer dari (Atas Nama) <span class="text-danger">*</span></label>
                    <input type="text" name="transfer_from" id="transfer_from"
                           class="form-control" required maxlength="255"
                           placeholder="Nama Pemilik Rekening" />
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Tanggal Transfer <span class="text-danger">*</span></label>
                    <input type="date" name="transfer_date" id="transfer_date"
                           class="form-control" required
                           value="{{ \Carbon\Carbon::today()->format('Y-m-d') }}" />
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Bukti Transfer <span class="text-danger">*</span></label>
                    <input type="file" name="payment_proof" id="payment_proof"
                           class="form-control" accept=".jpg,.jpeg,.png,.pdf" required />
                    <div class="form-text">Format: JPG, PNG, PDF. Maksimal 5MB.</div>
                    <div class="invalid-feedback" id="payment_proof_error"></div>
                </div>
            </div>

            <div class="d-grid mt-5">
                <button type="submit" class="btn btn-success btn-lg" id="submit_btn" disabled>
                    <span class="spinner-border spinner-border-sm align-middle me-2 d-none" id="submit_spinner"></span>
                    Kirim Bukti Transfer
                </button>
            </div>
        </form>
    </div>
</div>

@endif

@endsection

@push('scripts')
<script>
(function () {
    "use strict";

    var selectedInvoices = new Set();
    var totalAmount = 0;

    var formatRupiah = function (num) {
        return 'Rp' + num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    };

    var updateTotal = function () {
        var amountEl = document.getElementById('total_amount');
        amountEl.textContent = formatRupiah(totalAmount);
        document.getElementById('transfer_amount').value = totalAmount;
        document.getElementById('submit_btn').disabled = selectedInvoices.size === 0;
    };

    // Select all
    document.getElementById('select_all').addEventListener('change', function () {
        var checkboxes = document.querySelectorAll('.invoice-checkbox');
        checkboxes.forEach(function (cb) {
            cb.checked = this.checked;
            var id = cb.value;
            if (this.checked) {
                selectedInvoices.add(id);
            } else {
                selectedInvoices.delete(id);
            }
        });
        updateTotal();
    });

    // Individual checkbox
    document.querySelectorAll('.invoice-checkbox').forEach(function (cb) {
        cb.addEventListener('change', function () {
            var id = this.value;
            if (this.checked) {
                selectedInvoices.add(id);
            } else {
                selectedInvoices.delete(id);
            }

            // Update select all
            var allCheckboxes = document.querySelectorAll('.invoice-checkbox');
            var allChecked = Array.from(allCheckboxes).every(function (c) { return c.checked; });
            var someChecked = Array.from(allCheckboxes).some(function (c) { return c.checked; });
            document.getElementById('select_all').checked = allChecked;
            document.getElementById('select_all').indeterminate = someChecked && !allChecked;

            updateTotal();
        });
    });

    // Calculate total from selected
    document.querySelectorAll('.invoice-row').forEach(function (row) {
        row.querySelector('.invoice-checkbox').addEventListener('change', function () {
            var amount = parseInt(row.dataset.amount, 10) || 0;
            if (this.checked) {
                totalAmount += amount;
            } else {
                totalAmount -= amount;
            }
            updateTotal();
        });
    });

    // Form submit
    var form = document.getElementById('payment_form');
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            if (selectedInvoices.size === 0) {
                Swal.fire({ text: "Pilih minimal satu tagihan untuk dibayar.", icon: "warning", buttonsStyling: false, confirmButtonText: "Ok", customClass: { confirmButton: "btn btn-warning" } });
                return;
            }

            document.getElementById('invoice_ids').value = Array.from(selectedInvoices).join(',');

            var transferTo = document.getElementById('transfer_to').value;
            var transferAmount = parseInt(document.getElementById('transfer_amount').value, 10);
            var transferFrom = document.getElementById('transfer_from').value.trim();
            var transferDate = document.getElementById('transfer_date').value;
            var proofFile = document.getElementById('payment_proof').files[0];

            if (!transferTo || !transferAmount || !transferFrom || !transferDate || !proofFile) {
                Swal.fire({ text: "Lengkapi semua field yang wajib diisi.", icon: "warning", buttonsStyling: false, confirmButtonText: "Ok", customClass: { confirmButton: "btn btn-warning" } });
                return;
            }

            var submitBtn = document.getElementById('submit_btn');
            var spinner = document.getElementById('submit_spinner');
            submitBtn.disabled = true;
            spinner.classList.remove('d-none');

            var formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
            .then(function (res) {
                submitBtn.disabled = false;
                spinner.classList.add('d-none');
                if (res.status === 201) {
                    Swal.fire({
                        text: "Bukti pembayaran berhasil diupload. Tim kami akan segera memverifikasi dalam 1x24 jam.",
                        icon: "success",
                        buttonsStyling: false,
                        confirmButtonText: "Ok",
                        customClass: { confirmButton: "btn btn-success" }
                    }).then(function () {
                        window.location.reload();
                    });
                } else {
                    res.json().then(function (data) {
                        if (data.errors) {
                            Object.keys(data.errors).forEach(function (field) {
                                var errEl = document.getElementById(field + '_error') || document.getElementById('payment_proof_error');
                                if (errEl) { errEl.textContent = Array.isArray(data.errors[field]) ? data.errors[field][0] : data.errors[field]; errEl.style.display = 'block'; }
                            });
                        }
                        Swal.fire({ text: data.message || "Gagal mengirim bukti pembayaran.", icon: "error", buttonsStyling: false, confirmButtonText: "Ok", customClass: { confirmButton: "btn btn-danger" } });
                    }).catch(function () {
                        Swal.fire({ text: "Terjadi kesalahan saat mengirim.", icon: "error", buttonsStyling: false, confirmButtonText: "Ok", customClass: { confirmButton: "btn btn-danger" } });
                    });
                }
            })
            .catch(function () {
                submitBtn.disabled = false;
                spinner.classList.add('d-none');
            });
        });
    }

})();
</script>
@endpush
