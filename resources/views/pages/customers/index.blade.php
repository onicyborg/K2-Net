@extends('layouts.app')

@section('title', 'Pelanggan')

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

@section('content')
<div class="card card-flush">
  <div class="card-header pt-5">
    <div class="card-title">
      <div class="d-flex align-items-center position-relative my-1">
        <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-4"></i>
        <input type="search" class="form-control form-control-solid w-250px ps-12" placeholder="Cari pelanggan..." />
      </div>
    </div>
    <div class="card-toolbar">
      <div class="d-flex" role="tablist">
        <input type="checkbox" class="btn-check" id="filter_all" checked autocomplete="off" />
        <label class="btn btn-sm btn-color-muted btn-active btn-active-primary fw-semibold px-4 me-2" for="filter_all">Semua</label>
      </div>
    </div>
  </div>
  <div class="card-body pt-0">
    <table class="table align-middle table-row-dashed fs-6 gy-3" id="kt_table_customers">
      <thead>
        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
          <th class="min-w-150px">Nama</th>
          <th class="min-w-100px">Paket</th>
          <th class="min-w-100px">No. HP</th>
          <th class="min-w-100px">Status</th>
          <th class="min-w-100px">Tanggal Daftar</th>
          <th class="text-end min-w-70px">Aksi</th>
        </tr>
      </thead>
      <tbody class="fw-semibold text-gray-700">
        <tr>
          <td colspan="6" class="text-center text-muted py-10">Data belum tersedia. Jalankan migration dan seeder terlebih dahulu.</td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  var table = window.KTDatatablesDataSourceAjaxServer.init($('#kt_table_customers'), {
    processing: true,
    serverSide: true,
    ajax: {
      url: '{{ route('admin.customers.index') }}',
      type: 'GET',
    },
    columns: [
      { data: 'name' },
      { data: 'package.name' },
      { data: 'phone' },
      { data: 'status' },
      { data: 'created_at' },
      { data: 'actions', responsivePriority: -1 },
    ],
    language: { url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json' },
  });
});
</script>
@endpush
