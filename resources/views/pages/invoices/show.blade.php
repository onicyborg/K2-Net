@extends('layouts.app')

@section('title', 'Tagihan #' . $id)

@section('toolbar')
    <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 my-0">Tagihan #{{ $id }}</h1>
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Home</a>
        </li>
        <li class="breadcrumb-item">
            <span class="text-muted">/</span>
        </li>
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('admin.invoices.index') }}" class="text-muted text-hover-primary">Tagihan</a>
        </li>
        <li class="breadcrumb-item">
            <span class="text-muted">/</span>
        </li>
        <li class="breadcrumb-item text-gray-900 fw-bold">#{{ $id }}</li>
    </ul>
@endsection

@section('content')
<div class="row">
  <div class="col-md-8">
    <div class="card card-flush">
      <div class="card-header pt-5">
        <h3 class="card-title">Informasi Tagihan</h3>
      </div>
      <div class="card-body py-5">
        <div class="text-center text-muted py-10">Detail invoice akan ditampilkan di sini.</div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card card-flush">
      <div class="card-header pt-5">
        <h3 class="card-title">Aksi</h3>
      </div>
      <div class="card-body py-5">
        <a href="{{ route('admin.invoices.index') }}" class="btn btn-light w-100">Kembali ke Daftar</a>
      </div>
    </div>
  </div>
</div>
@endsection
