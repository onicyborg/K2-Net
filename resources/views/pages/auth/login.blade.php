@extends('layouts.auth')

@section('title', 'Login Admin — K2-Net')

@section('content')
<div class="card card-flush w-lg-500px mx-auto py-10">
    <div class="card-body py-15 py-lg-10">

        <div class="mb-5 text-center">
            <img alt="Logo" src="{{ url('images/logo-text-large.png') }}"
                 class="h-40px mb-5 mx-auto theme-logo theme-logo-light" />
            <img alt="Logo" src="{{ url('images/logo-text-dark-large.png') }}"
                 class="h-40px mb-5 mx-auto theme-logo theme-logo-dark" />
            <h1 class="fw-bolder text-gray-900 mb-2">K2-Net Admin</h1>
            <p class="text-gray-500 fw-semibold">Sistem Manajemen Tagihan & Pelanggan</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger d-flex align-items-center p-5 mb-5">
                <i class="ki-duotone ki-cross-circle fs-2 me-4 text-danger">
                    <span class="path1"></span><span class="path2"></span>
                </i>
                <div class="d-flex flex-column">
                    <h4 class="mb-1 text-danger">Login Gagal</h4>
                    <span>{{ $errors->first() }}</span>
                </div>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success d-flex align-items-center p-5 mb-5">
                <i class="ki-duotone ki-check-circle fs-2 me-4 text-success">
                    <span class="path1"></span><span class="path2"></span>
                </i>
                <div class="d-flex flex-column">
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="fv-row mb-5">
                <label class="form-label fs-6 fw-semibold required">Email</label>
                <input type="email" class="form-control form-control-lg form-control-solid"
                       name="email" autocomplete="email"
                       value="{{ old('email') }}" required autofocus />
            </div>
            <div class="fv-row mb-5">
                <label class="form-label fs-6 fw-semibold required">Password</label>
                <input type="password" class="form-control form-control-lg form-control-solid"
                       name="password" autocomplete="current-password" required />
            </div>
            <div class="fv-row mb-10">
                <label class="form-check form-check-custom form-check-solid me-5">
                    <input class="form-check-input" type="checkbox" name="remember" />
                    <span class="form-check-label text-gray-600">Ingat saya</span>
                </label>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg">Masuk</button>
            </div>
        </form>

        <div class="text-gray-500 fw-semibold fs-5 mt-5 text-center">
            <a href="{{ route('portal.login') }}" class="link-primary fw-bold">
                Login sebagai Pelanggan
            </a>
        </div>

    </div>
</div>
@endsection
