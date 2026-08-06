@extends('layouts.app')

@section('title', 'Konfigurasi Sistem')

@section('toolbar')
    <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 my-0">Konfigurasi Sistem</h1>
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Home</a>
        </li>
        <li class="breadcrumb-item">
            <span class="text-muted">/</span>
        </li>
        <li class="breadcrumb-item text-gray-900 fw-bold">Konfigurasi Sistem</li>
    </ul>
@endsection

@section('content')
<div class="card card-flush">
  <div class="card-body py-5">
    <div class="text-center text-muted py-10">Pengaturan sistem akan ditampilkan di sini.</div>
  </div>
</div>
@endsection
