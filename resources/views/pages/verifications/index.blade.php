@extends('layouts.app')

@section('title', 'Verifikasi Pembayaran')

@section('toolbar')
    <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 my-0">Verifikasi Pembayaran</h1>
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Home</a>
        </li>
        <li class="breadcrumb-item">
            <span class="text-muted">/</span>
        </li>
        <li class="breadcrumb-item text-gray-900 fw-bold">Verifikasi Pembayaran</li>
    </ul>
@endsection

@section('content')
<div class="card card-flush">
  <div class="card-body pt-0">
    <table class="table align-middle table-row-dashed fs-6 gy-3" id="kt_table_verifications">
      <thead>
        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
          <th class="min-w-100px">Invoice #</th>
          <th class="min-w-150px">Pelanggan</th>
          <th class="min-w-100px text-end">Jumlah Bayar</th>
          <th class="min-w-100px">Bukti</th>
          <th class="min-w-100px">Tanggal Upload</th>
          <th class="text-end min-w-100px">Aksi</th>
        </tr>
      </thead>
      <tbody class="fw-semibold text-gray-700">
        <tr>
          <td colspan="6" class="text-center text-muted py-10">Tidak ada pembayaran yang menunggu verifikasi.</td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
@endsection
