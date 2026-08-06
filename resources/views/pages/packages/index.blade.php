@extends('layouts.app')

@section('title', 'Paket Internet')

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

@section('content')
<div class="card card-flush">
  <div class="card-header pt-5">
    <div class="card-title">
      <div class="d-flex align-items-center position-relative my-1">
        <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-4"></i>
        <input type="search" class="form-control form-control-solid w-250px ps-12" placeholder="Cari paket..." />
      </div>
    </div>
  </div>
  <div class="card-body pt-0">
    <table class="table align-middle table-row-dashed fs-6 gy-3" id="kt_table_packages">
      <thead>
        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
          <th class="min-w-150px">Nama Paket</th>
          <th class="min-w-100px">Kecepatan</th>
          <th class="min-w-100px text-end">Harga/Bulan</th>
          <th class="min-w-100px text-end">Pelanggan</th>
          <th class="min-w-100px">Status</th>
          <th class="text-end min-w-70px">Aksi</th>
        </tr>
      </thead>
      <tbody class="fw-semibold text-gray-700">
        <tr>
          <td colspan="6" class="text-center text-muted py-10">Data belum tersedia.</td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
@endsection
