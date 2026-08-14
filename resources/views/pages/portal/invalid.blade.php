@extends('layouts.portal')

@section('title', 'Kode Tidak Valid — K2-Net')

@section('content')

<div class="card card-flush" style="max-width: 500px; margin: 0 auto;">
    <div class="card-body py-15 text-center">
        <div class="mb-5">
            <i class="ki-duotone ki-information-5 fs-5x text-warning"></i>
        </div>
        <h3 class="text-gray-900 fw-bold mb-3">Kode Akses Tidak Ditemukan</h3>
        <p class="text-gray-500 mb-5">
            Kode yang Anda masukkan tidak valid atau sudah kadaluarsa.<br />
            Silakan hubungi tim kami untuk mendapatkan kode akses baru.
        </p>
        <a href="mailto:info@k2net.com" class="btn btn-primary">
            Hubungi Kami
        </a>
    </div>
</div>

@endsection
