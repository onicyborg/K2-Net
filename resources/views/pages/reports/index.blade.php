@extends('layouts.app')

@section('title', 'Pelaporan')

@section('toolbar')
    <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 my-0">Pelaporan</h1>
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Home</a>
        </li>
        <li class="breadcrumb-item">
            <span class="text-muted">/</span>
        </li>
        <li class="breadcrumb-item text-gray-900 fw-bold">Pelaporan</li>
    </ul>
@endsection

@section('content')
<div class="row gx-5 gx-xl-10">
  <div class="col-xl-4">
    <div class="card card-flush">
      <div class="card-header pt-5">
        <h3 class="card-title">Filter Laporan</h3>
      </div>
      <div class="card-body py-5">
        <div class="mb-3">
          <label class="form-label">Jenis Laporan</label>
          <select class="form-select form-select-solid">
            <option value="revenue">Pendapatan</option>
            <option value="customers">Pelanggan</option>
            <option value="invoices">Tagihan</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Dari Tanggal</label>
          <input type="date" class="form-control form-control-solid" />
        </div>
        <div class="mb-3">
          <label class="form-label">Sampai Tanggal</label>
          <input type="date" class="form-control form-control-solid" />
        </div>
        <button class="btn btn-primary w-100">Tampilkan</button>
      </div>
    </div>
  </div>
  <div class="col-xl-8">
    <div class="card card-flush">
      <div class="card-header pt-5">
        <h3 class="card-title">Hasil Laporan</h3>
      </div>
      <div class="card-body py-10 text-center text-muted">
        Pilih filter dan klik "Tampilkan" untuk melihat laporan.
      </div>
    </div>
  </div>
</div>
@endsection
