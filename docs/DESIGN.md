# Metronic v8.2.9 — Design & Usage Guide (K2-Net)

## Overview

Template: **Metronic v8.2.9** oleh KeenThemes
Domain CDN Assets: `https://magang.skripsian.site/assets`
Project ini digunakan sebagai skill/referensi untuk development project Laravel dengan Blade Template Engine.

---

## 1. Asset Structure (CDN)

Semua asset berada di `https://magang.skripsian.site/assets`. Asset bundle sudah di-deploy di server dan siap dipanggil langsung.

### Path Assets CDN

```
https://magang.skripsian.site/assets/
├── css/
│   └── style.bundle.css
├── js/
│   ├── scripts.bundle.js
│   └── widgets.bundle.js
├── plugins/
│   ├── global/
│   │   ├── plugins.bundle.css
│   │   └── plugins.bundle.js
│   └── custom/
│       ├── fullcalendar/
│       │   ├── fullcalendar.bundle.css
│       │   └── fullcalendar.bundle.js
│       └── datatables/
│           └── datatables.bundle.css / .js
├── media/
│   ├── avatars/          → foto user, avatar
│   ├── banners/          → gambar banner
│   ├── logos/            → logo brand (favicon, default.svg, landing.svg, dll)
│   ├── illustrations/     → gambar ilustrasi (sketchy, misc)
│   ├── stock/            → foto stock (produk, 600x600, 900x600, dll)
│   ├── auth/             → background auth (bg1.jpg, bg1-dark.jpg, 404-error.png)
│   ├── svg/
│   │   ├── brand-logos/  → logo brand (google, github, figma, dll)
│   │   └── illustrations/→ SVG ilustrasi
│   └── icons/            → icon library
└── custom/
    └── (file JS custom per-feature)
```

### Vendor & Third-Party Scripts (CDN Eksternal)

```html
<!-- Google Fonts (wajib) -->
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />

<!-- Chart Library (amCharts) -->
<script src="https://cdn.amcharts.com/lib/5/index.js"></script>
<script src="https://cdn.amcharts.com/lib/5/xy.js"></script>
<script src="https://cdn.amcharts.com/lib/5/percent.js"></script>
<script src="https://cdn.amcharts.com/lib/5/radar.js"></script>
<script src="https://cdn.amcharts.com/lib/5/themes/Animated.js"></script>
<script src="https://cdn.amcharts.com/lib/5/map.js"></script>

<!-- SweetAlert2 sudah termasuk di plugins.bundle.js -->
<!-- Select2 sudah termasuk di plugins.bundle.js -->
```

---

## 2. Global Stylesheet & Javascript (Mandatory)

Pastikan semua halaman memuat bundle ini (pakai `asset()` helper, asumsikan `ASSET_URL=https://magang.skripsian.site` di `.env`):

```html
<!-- Fonts -->
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />

<!-- Vendor Stylesheets (opsional, sesuai kebutuhan halaman) -->
<link href="{{ asset('assets/plugins/custom/fullcalendar/fullcalendar.bundle.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />

<!-- Global Stylesheets Bundle (WAJIB) -->
<link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />

<!-- Frame-busting Script -->
<script>
  if (window.top != window.self) { window.top.location.replace(window.self.location.href); }
</script>
```

```html
<!-- Javascript (sebelum </body>) -->

<!-- Global Javascript Bundle (WAJIB) -->
<script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
<script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>

<!-- Vendor Scripts (sesuaikan kebutuhan) -->
<script src="{{ asset('assets/plugins/custom/fullcalendar/fullcalendar.bundle.js') }}"></script>
<script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>

<!-- Custom Page Scripts -->
<script src="{{ asset('assets/js/widgets.bundle.js') }}"></script>
<script src="{{ asset('assets/js/custom/widgets.js') }}"></script>
```

---

## 3. Layout Types

### 3.1 App Layout (Sidebar + Header) — `app-default`

> **Struktur FINAL — semua page Admin K2-Net menggunakan layout ini:**

```html
<body id="kt_app_body"
      data-kt-app-layout="dark-sidebar"
      data-kt-app-header-fixed="true"
      data-kt-app-sidebar-enabled="true"
      data-kt-app-sidebar-fixed="true"
      data-kt-app-sidebar-hoverable="true"
      data-kt-app-sidebar-push-header="true"
      data-kt-app-sidebar-push-toolbar="true"
      data-kt-app-sidebar-push-footer="true"
      data-kt-app-toolbar-enabled="false"
      class="app-default">

  <div class="d-flex flex-column flex-root" id="kt_app_root">
    <!-- App Page -->
    <div class="app-page flex-column flex-column-fluid" id="kt_app_page">

      <!-- HEADER (di dalam app-page) -->
      <div id="kt_app_header" class="app-header" data-kt-sticky="true"
           data-kt-sticky-activate="{default: true, lg: true}"
           data-kt-sticky-name="app-header-minimize"
           data-kt-sticky-offset="{default: '200px', lg: '0'}">
        <div class="app-container container-fluid d-flex align-items-stretch justify-content-between"
             id="kt_app_header_container">
          <!-- Sidebar mobile toggle, mobile logo, header wrapper (navbar) -->
        </div>
      </div>

      <!-- WRAPPER: sidebar + main DI DALAM wrapper ini -->
      <div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">

        <!-- SIDEBAR (di dalam app-wrapper, SEBELUM app-main) -->
        <div id="kt_app_sidebar" class="app-sidebar flex-column" ...>
          <!-- sidebar logo + menu + footer -->
        </div>

        <!-- MAIN CONTENT (di dalam app-wrapper, SETELAH sidebar) -->
        <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
          <div class="d-flex flex-column flex-column-fluid">

            <!-- CONTENT AREA -->
            <div id="kt_app_content" class="app-content flex-column-fluid">
              <div class="app-container container-fluid">
                <!-- === PAGE CONTENT GOES HERE === -->
              </div>
            </div>
          </div>

          <!-- FOOTER -->
          <div class="app-footer py-3 d-flex flex-column flex-md-row flex-center flex-md-stack"
               id="kt_app_footer">
            ...
          </div>
        </div>
        <!-- End Main -->
      </div>
      <!-- End Wrapper -->
    </div>
    <!-- End Page -->
  </div>
  <!-- End Root -->
</body>
```

**Urutan DOM yang BENAR:**
```
kt_app_body
└── kt_app_root
    └── kt_app_page
        ├── kt_app_header
        └── kt_app_wrapper
            ├── kt_app_sidebar     ← sidebar di dalam wrapper
            └── kt_app_main        ← main juga di dalam wrapper
                ├── flex-column-fluid
                │   └── kt_app_content
                └── kt_app_footer
```

### 3.2 Customer Portal Layout (Simplified — tanpa sidebar kompleks)

Portal pelanggan menggunakan layout yang lebih sederhana dengan header minimal:

```html
<body id="kt_body" class="app-blank bgi-size-cover bgi-position-center bgi-no-repeat">
  <style>
    body {
      background-image: url('https://magang.skripsian.site/assets/media/auth/bg1.jpg');
    }
    [data-bs-theme="dark"] body {
      background-image: url('https://magang.skripsian.site/assets/media/auth/bg1-dark.jpg');
    }
  </style>

  <!-- Topbar simple untuk portal -->
  <div class="d-flex flex-column flex-root" id="kt_app_root">
    <div class="app-page flex-column flex-column-fluid" id="kt_app_page">
      <!-- Customer header bar -->
      <div id="kt_app_header" class="app-header" ...>
        ...
      </div>

      <!-- Content tanpa sidebar -->
      <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div id="kt_app_content" class="app-content flex-column-fluid">
          <div class="app-container container-fluid">
            <!-- === CUSTOMER PORTAL CONTENT === -->
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
```

### 3.3 Blank Layout (Tanpa sidebar/header) — `app-blank`

```html
<body id="kt_body" class="app-blank bgi-size-cover bgi-position-center bgi-no-repeat">
  <!-- Background image -->
  <style>body { background-image: url('https://magang.skripsian.site/assets/media/auth/bg1.jpg'); }</style>

  <div class="d-flex flex-column flex-root" id="kt_app_root">
    <!-- Content here -->
  </div>
</body>
```

---

## 4. Theme Mode Setup (Wajib)

Theme mode handler hidup di `layouts/app.blade.php`, setelah `scripts.bundle.js`:

```html
<!-- Theme Mode Handler -->
<script>
(function () {
    "use strict";

    function getThemeMode() {
        var stored = localStorage.getItem("data-bs-theme");
        if (stored === "system") {
            return window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
        }
        return stored || "light";
    }

    function applyTheme(mode) {
        if (mode === "system") {
            mode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
        }
        document.documentElement.setAttribute("data-bs-theme", mode);
        localStorage.setItem("data-bs-theme", mode);

        var lightIcon = document.querySelector(".theme-light-show");
        var darkIcon  = document.querySelector(".theme-dark-show");
        if (lightIcon && darkIcon) {
            lightIcon.style.display = mode === "light" ? "inline" : "none";
            darkIcon.style.display  = mode === "dark"  ? "inline" : "none";
        }
    }

    applyTheme(localStorage.getItem("data-bs-theme") || "light");

    document.addEventListener("click", function (e) {
        var trigger = e.target.closest("[data-kt-element=\"mode\"]");
        if (!trigger) return;
        var mode = trigger.getAttribute("data-kt-value");
        if (!mode) return;
        applyTheme(mode);
    });
})();
</script>
```

---

## 5. Page Title (Toolbar / Breadcrumb)

Toolbar berada di atas area konten (`#kt_app_toolbar`):

```html
<div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
  <div class="app-container container-fluid d-flex flex-stack">
    <!-- Page title -->
    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
      <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
        Nama Halaman
      </h1>
      <!-- Breadcrumb -->
      <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
        <li class="breadcrumb-item text-muted">
          <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Home</a>
        </li>
        <li class="breadcrumb-item">
          <span class="text-muted">/</span>
        </li>
        <li class="breadcrumb-item text-muted">Menu</li>
        <li class="breadcrumb-item">
          <span class="text-muted">/</span>
        </li>
        <li class="breadcrumb-item text-gray-900 fw-bold">Submenu</li>
      </ul>
    </div>

    <!-- Toolbar actions -->
    <div class="d-flex align-items-center gap-2 gap-lg-3">
      <a href="#" class="btn btn-sm btn-primary">Action</a>
    </div>
  </div>
</div>
```

---

## 6. Sidebar Menu (Admin)

### 6.1 Sidebar Structure

```html
<div id="kt_app_sidebar" class="app-sidebar flex-column"
     data-kt-drawer="true"
     data-kt-drawer-name="app-sidebar"
     data-kt-drawer-activate="{default: true, lg: false}"
     data-kt-drawer-overlay="true"
     data-kt-drawer-width="250px"
     data-kt-drawer-direction="start"
     data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">

  <!-- Sidebar Logo -->
  <div class="app-sidebar-logo px-6" id="kt_app_sidebar_logo">
    <a href="{{ route('admin.dashboard') }}">
      <img alt="Logo" src="{{ asset('assets/media/logos/default-dark.svg') }}"
           class="h-25px app-sidebar-logo-default transition-opacity" />
      <img alt="Logo" src="{{ asset('assets/media/logos/default-small.svg') }}"
           class="h-20px app-sidebar-logo-minimize transition-opacity" />
    </a>
  </div>

  <!-- Menu dengan scroll wrapper -->
  <div class="app-sidebar-menu overflow-hidden flex-column-fluid" id="kt_app_sidebar_menu">
      <div class="app-sidebar-wrapper">
          <div id="kt_app_sidebar_menu_scroll"
               class="scroll-y my-5 mx-3"
               data-kt-scroll="true"
               data-kt-scroll-height="auto"
               data-kt-scroll-dependencies="#kt_app_sidebar_logo, #kt_app_sidebar_footer"
               data-kt-scroll-save-state="true"
               data-kt-scroll-wrappers="#kt_app_sidebar_menu_scroll">

              <div class="menu menu-column menu-rounded menu-sub-indention fw-semibold fs-6"
                   id="#kt_app_sidebar_menu"
                   data-kt-menu="true"
                   data-kt-menu-expand="false">

                <!-- Menu items di sini -->

              </div>
          </div>
      </div>
  </div>

  <!-- Sidebar Footer Button -->
  <div class="app-sidebar-footer flex-column-auto pt-2 pb-6 px-6" id="kt_app_sidebar_footer">
    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit" class="btn btn-flex flex-center btn-custom btn-primary overflow-hidden text-nowrap px-0 h-40px w-100">
        <span class="btn-label">Logout</span>
        <i class="ki-duotone ki-exit-right-corner btn-icon fs-2 m-0">
          <span class="path1"></span><span class="path2"></span>
        </i>
      </button>
    </form>
  </div>
</div>
```

### 6.2 Menu Items — K2-Net Admin

```html
<!-- === DASHBOARD === -->
<div class="menu-item">
  <a class="menu-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
     href="{{ route('admin.dashboard') }}">
    <span class="menu-icon">
      <i class="ki-duotone ki-home fs-2">
        <span class="path1"></span><span class="path2"></span>
      </i>
    </span>
    <span class="menu-title">Dashboard</span>
  </a>
</div>

<!-- === MASTER DATA (Accordion) === -->
<div data-kt-menu-trigger="click" class="menu-item menu-accordion">
  <span class="menu-link">
    <span class="menu-icon">
      <i class="ki-duotone ki-element-plus fs-2">
        <span class="path1"></span><span class="path2"></span>
      </i>
    </span>
    <span class="menu-title">Master Data</span>
    <span class="menu-arrow"></span>
  </span>
  <div class="menu-sub menu-sub-accordion">
    <div class="menu-item">
      <a class="menu-link {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}"
         href="{{ route('admin.customers.index') }}">
        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
        <span class="menu-title">Pelanggan</span>
      </a>
    </div>
    <div class="menu-item">
      <a class="menu-link {{ request()->routeIs('admin.packages.*') ? 'active' : '' }}"
         href="{{ route('admin.packages.index') }}">
        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
        <span class="menu-title">Paket Internet</span>
      </a>
    </div>
  </div>
</div>

<!-- === TAGIHAN === -->
<div class="menu-item">
  <a class="menu-link {{ request()->routeIs('admin.invoices.*') ? 'active' : '' }}"
     href="{{ route('admin.invoices.index') }}">
    <span class="menu-icon">
      <i class="ki-duotone ki-document fs-2">
        <span class="path1"></span><span class="path2"></span>
      </i>
    </span>
    <span class="menu-title">Tagihan</span>
  </a>
</div>

<!-- === VERIFIKASI PEMBAYARAN === -->
<div class="menu-item">
  <a class="menu-link {{ request()->routeIs('admin.verifications.*') ? 'active' : '' }}"
     href="{{ route('admin.verifications.index') }}">
    <span class="menu-icon">
      <i class="ki-duotone ki-check-circle fs-2">
        <span class="path1"></span><span class="path2"></span>
      </i>
    </span>
    <span class="menu-title">Verifikasi Pembayaran</span>
    <!-- Badge untuk jumlah pending -->
    @if($pendingVerificationCount > 0)
      <span class="menu-badge">
        <span class="badge badge-light-danger">{{ $pendingVerificationCount }}</span>
      </span>
    @endif
  </a>
</div>

<!-- === PELAPORAN === -->
<div class="menu-item">
  <a class="menu-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}"
     href="{{ route('admin.reports.index') }}">
    <span class="menu-icon">
      <i class="ki-duotone ki-chart-line-up fs-2">
        <span class="path1"></span><span class="path2"></span>
      </i>
    </span>
    <span class="menu-title">Pelaporan</span>
  </a>
</div>

<!-- === LOG NOTIFIKASI === -->
<div class="menu-item">
  <a class="menu-link {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}"
     href="{{ route('admin.notifications.index') }}">
    <span class="menu-icon">
      <i class="ki-duotone ki-send fs-2">
        <span class="path1"></span><span class="path2"></span>
      </i>
    </span>
    <span class="menu-title">Log Notifikasi</span>
  </a>
</div>

<!-- === KONFIGURASI === -->
<div class="menu-item">
  <a class="menu-link {{ request()->routeIs('admin.configurations.*') ? 'active' : '' }}"
     href="{{ route('admin.configurations.index') }}">
    <span class="menu-icon">
      <i class="ki-duotone ki-gear fs-2">
        <span class="path1"></span><span class="path2"></span>
      </i>
    </span>
    <span class="menu-title">Konfigurasi</span>
  </a>
</div>
```

### 6.3 Sidebar — Kesalahan Umum

```blade
{{-- ❌ SALAH: Tambahan padding/margin langsung pada #kt_app_sidebar --}}
<div id="kt_app_sidebar" class="app-sidebar py-7 px-4">

{{-- ❌ SALAH: Width berbeda dari yang di-register Metronic --}}
data-kt-drawer-width="300px"  {{-- seharusnya 225px atau 250px --}}
```

---

## 7. Header (Topbar)

Header Navbar (`#kt_app_header`) berisi logo, search, notifications, user menu:

### 7.1 Logo (Desktop & Mobile)

```html
<!-- Desktop logo (visible di desktop, hidden di mobile) -->
<a href="{{ route('admin.dashboard') }}" class="d-none d-lg-flex">
  <img alt="Logo" src="{{ asset('assets/media/logos/default-dark.svg') }}" class="h-25px" />
</a>

<!-- Mobile logo (visible di mobile, hidden di desktop) -->
<a href="{{ route('admin.dashboard') }}" class="d-lg-none">
  <img alt="Logo" src="{{ asset('assets/media/logos/default-small.svg') }}" class="h-30px" />
</a>
```

### 7.2 User Menu (Admin Header)

```html
<div class="app-navbar flex-shrink-0">

  <!-- THEME MODE TOGGLE -->
  <div class="app-navbar-item ms-1 ms-md-4">
    <a href="#" class="btn btn-icon btn-custom btn-icon-muted btn-active-light btn-active-color-primary w-35px h-35px"
       data-kt-menu-trigger="{default:'click', lg: 'hover'}" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">
      <i class="ki-duotone ki-night-day theme-light-show fs-1">
        <span class="path1"></span><span class="path2"></span>...
      </i>
      <i class="ki-duotone ki-moon theme-dark-show fs-1">
        <span class="path1"></span><span class="path2"></span>
      </i>
    </a>
    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-title-gray-700 menu-icon-gray-500 menu-active-bg menu-state-color fw-semibold py-4 fs-base w-150px" data-kt-menu="true">
      <div class="menu-item px-3 my-0">
        <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="light">
          <span class="menu-icon"><i class="ki-duotone ki-night-day fs-2">...</i></span>
          <span class="menu-title">Light</span>
        </a>
      </div>
      <div class="menu-item px-3 my-0">
        <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="dark">
          <span class="menu-icon"><i class="ki-duotone ki-moon fs-2">...</i></span>
          <span class="menu-title">Dark</span>
        </a>
      </div>
      <div class="menu-item px-3 my-0">
        <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="system">
          <span class="menu-icon"><i class="ki-duotone ki-screen fs-2">...</i></span>
          <span class="menu-title">System</span>
        </a>
      </div>
    </div>
  </div>

  <!-- USER MENU -->
  <div class="app-navbar-item ms-1 ms-md-4" id="kt_header_user_menu_toggle">
    <div class="cursor-pointer symbol symbol-35px"
         data-kt-menu-trigger="{default: 'click', lg: 'hover'}"
         data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">
      <img src="{{ asset('assets/media/avatars/300-3.jpg') }}" class="rounded-3" alt="user" />
    </div>
    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-color fw-semibold py-4 fs-6 w-275px" data-kt-menu="true">
      <div class="menu-item px-3">
        <div class="menu-content d-flex align-items-center px-3">
          <div class="symbol symbol-50px me-5">
            <img alt="Logo" src="{{ asset('assets/media/avatars/300-3.jpg') }}" />
          </div>
          <div class="d-flex flex-column">
            <div class="fw-bold d-flex align-items-center fs-5">{{ Auth::user()->name }}</div>
            <a href="#" class="fw-semibold text-muted text-hover-primary fs-7">{{ Auth::user()->email }}</a>
          </div>
        </div>
      </div>
      <div class="separator my-2"></div>
      <div class="menu-item px-5">
        <a href="{{ route('profile.edit') }}" class="menu-link px-5">Profil Saya</a>
      </div>
      <div class="separator my-2"></div>
      <div class="menu-item px-5">
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="btn btn-sm btn-danger w-100">Logout</button>
        </form>
      </div>
    </div>
  </div>
</div>
```

---

## 8. Card Component (K2-Net Billing)

### 8.1 Dashboard Stat Cards

```html
<div class="row gx-5 gx-xl-10 mb-5 mb-xl-10">
  <!-- Total Pelanggan Aktif -->
  <div class="col-sm-6 col-xl-4 mb-5 mb-xl-10">
    <div class="card card-flush h-md-50">
      <div class="card-header pt-5">
        <div class="card-title d-flex flex-column">
          <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">{{ number_format($stats['active_customers']) }}</span>
          <span class="text-gray-500 pt-1 fw-semibold fs-6">Pelanggan Aktif</span>
        </div>
      </div>
      <div class="card-body d-flex align-items-end pt-0">
        <div class="d-flex align-items-center flex-column mt-3 w-100">
          <span class="badge badge-light-success">
            <i class="ki-duotone ki-arrow-up fs-5 text-success ms-n1">
              <span class="path1"></span><span class="path2"></span>
            </i>
            {{ $stats['new_customers_this_month'] }} bulan ini
          </span>
        </div>
      </div>
    </div>
  </div>

  <!-- Total Pendapatan Bulan Ini -->
  <div class="col-sm-6 col-xl-4 mb-5 mb-xl-10">
    <div class="card card-flush h-md-50"
         style="background: linear-gradient(90deg, #009ef7 0%, #2ceef0 100%)">
      <div class="card-header pt-5">
        <div class="card-title d-flex flex-column">
          <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">Rp{{ number_format($stats['revenue_this_month'], 0, ',', '.') }}</span>
          <span class="text-white text-opacity-75 pt-1 fw-semibold fs-6">Pendapatan Bulan Ini</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Total Piutang -->
  <div class="col-sm-6 col-xl-4 mb-5 mb-xl-10">
    <div class="card card-flush h-md-50"
         style="background: linear-gradient(90deg, #f1416c 0%, #f98d30 100%)">
      <div class="card-header pt-5">
        <div class="card-title d-flex flex-column">
          <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">Rp{{ number_format($stats['total_receivables'], 0, ',', '.') }}</span>
          <span class="text-white text-opacity-75 pt-1 fw-semibold fs-6">Total Piutang</span>
        </div>
      </div>
    </div>
  </div>
</div>
```

### 8.2 Card dengan Tab/Toolbar (Invoice List)

```html
<div class="card card-flush">
  <div class="card-header pt-5">
    <div class="card-title d-flex flex-column">
      <span class="fs-2hx fw-bold text-gray-900 me-2">Daftar Tagihan</span>
    </div>
    <div class="card-toolbar">
      <ul class="nav" id="kt_invoice_tabs">
        <li class="nav-item">
          <a class="nav-link btn btn-sm btn-color-muted btn-active btn-active-light fw-bold px-4 me-1"
             data-bs-toggle="tab" href="#all">Semua</a>
        </li>
        <li class="nav-item">
          <a class="nav-link btn btn-sm btn-color-muted btn-active btn-active-light fw-bold px-4 me-1"
             data-bs-toggle="tab" href="#unpaid">Belum Bayar</a>
        </li>
        <li class="nav-item">
          <a class="nav-link btn btn-sm btn-color-muted btn-active btn-active-light fw-bold px-4 active"
             data-bs-toggle="tab" href="#paid">Lunas</a>
        </li>
      </ul>
    </div>
  </div>
  <div class="card-body pt-6">
    <div class="tab-content">
      <div class="tab-pane fade show active" id="all" role="tabpanel">
        <!-- Table content -->
      </div>
    </div>
  </div>
</div>
```

---

## 9. Buttons

### 9.1 Button Variants (K2-Net Specific)

```html
<!-- Primary (Simpan, Approve) -->
<button class="btn btn-primary">Simpan</button>

<!-- Success (Approve payment) -->
<button class="btn btn-success">
  <i class="ki-duotone ki-check fs-2"><span class="path1"></span><span class="path2"></span></i>
  Setujui
</button>

<!-- Danger (Reject payment) -->
<button class="btn btn-danger">
  <i class="ki-duotone ki-cross-circle fs-2"><span class="path1"></span><span class="path2"></span></i>
  Tolak
</button>

<!-- Light (Batal) -->
<button class="btn btn-light" data-bs-dismiss="modal">Batal</button>

<!-- Ghost -->
<button class="btn btn-active-light">Kembali</button>
```

### 9.2 Button with Icon (K2-Net)

```html
<!-- Add new -->
<button class="btn btn-primary">
  <i class="ki-duotone ki-plus fs-2"><span class="path1"></span><span class="path2"></span></i>
  Tambah Baru
</button>

<!-- Export -->
<button class="btn btn-light-primary">
  <i class="ki-duotone ki-arrows-loop fs-2"><span class="path1"></span><span class="path2"></span></i>
  Export
</button>

<!-- Icon button -->
<button class="btn btn-icon btn-bg-light btn-color-muted btn-active-light-primary w-30px h-30px"
        data-bs-toggle="tooltip" title="Detail">
  <i class="ki-duotone ki-eye fs-2"><span class="path1"></span><span class="path2"></span></i>
</button>
```

---

## 10. Badges — Invoice & Customer Status

### 10.1 Invoice Status Badges

```html
<!-- Belum Bayar -->
<span class="badge badge-light-danger">Belum Bayar</span>

<!-- Menunggu Verifikasi -->
<span class="badge badge-light-warning">Menunggu Verifikasi</span>

<!-- Lunas -->
<span class="badge badge-light-success">Lunas</span>

<!-- Ditolak -->
<span class="badge badge-light-dark">Ditolak</span>
```

### 10.2 Customer Status Badges

```html
<!-- Aktif -->
<span class="badge badge-light-success">Aktif</span>

<!-- Isolir -->
<span class="badge badge-light-warning">Isolir</span>

<!-- Nonaktif -->
<span class="badge badge-light-dark">Nonaktif</span>
```

### 10.3 Notification Status Badges

```html
<!-- Pending -->
<span class="badge badge-light-info">Pending</span>

<!-- Terkirim -->
<span class="badge badge-light-success">Terkirim</span>

<!-- Gagal -->
<span class="badge badge-light-danger">Gagal</span>
```

### 10.4 Menu Badge (for pending verification count)

```html
<span class="menu-badge">
  <span class="badge badge-light-danger badge-circle fw-bold fs-7">{{ $pendingCount }}</span>
</span>
```

---

## 11. Forms

### 11.1 Text Input (Pelanggan)

```html
<div class="fv-row mb-7">
  <label class="fs-6 fw-semibold form-label mt-3">
    <span class="required">Nama Lengkap</span>
  </label>
  <input type="text" class="form-control form-control-solid"
         name="name" placeholder="Masukkan nama lengkap" value="" />
</div>
```

### 11.2 WhatsApp Number Input

```html
<div class="fv-row mb-7">
  <label class="fs-6 fw-semibold form-label mt-3">
    <span class="required">Nomor WhatsApp</span>
  </label>
  <div class="input-group">
    <span class="input-group-text">+62</span>
    <input type="text" class="form-control form-control-solid"
           name="whatsapp_number" placeholder="8123456789"
           data-inputmask="'mask': '999999999999'" />
  </div>
  <div class="fv-plugins-message-container invalid-feedback">
    <div data-field="whatsapp_number" data-validator="callback"></div>
  </div>
</div>
```

### 11.3 Email Input

```html
<div class="fv-row mb-7">
  <label class="fs-6 fw-semibold form-label mt-3">
    <span class="required">Email</span>
  </label>
  <input type="email" class="form-control form-control-solid"
         name="email" placeholder="email@example.com" value="" />
</div>
```

### 11.4 Select / Dropdown (Package)

```html
<select class="form-select form-select-solid form-select-lg"
        name="package_id" data-control="select2" data-placeholder="Pilih paket">
  <option value="">Pilih paket...</option>
  @foreach($packages as $package)
    <option value="{{ $package->id }}">
      {{ $package->name }} — Rp{{ number_format($package->price, 0, ',', '.') }}/bulan
    </option>
  @endforeach
</select>
```

### 11.5 Customer Status Select

```html
<select class="form-select form-select-solid" name="status" data-control="select2">
  <option value="aktif" selected>Aktif</option>
  <option value="isolir">Isolir</option>
  <option value="nonaktif">Nonaktif</option>
</select>
```

### 11.6 File Upload (Bukti Transfer)

```html
<div class="fv-row mb-7">
  <label class="fs-6 fw-semibold form-label mt-3">
    <span class="required">Bukti Transfer</span>
  </label>
  <input type="file" class="form-control form-control-solid"
         name="payment_proof" accept=".pdf,.jpg,.jpeg,.png"
         id="payment_proof_input" />
  <div class="fv-plugins-message-container invalid-feedback">
    <div data-field="payment_proof" data-validator="file"></div>
  </div>
  <div class="form-text">Format: PDF, JPG, PNG. Maks: 5MB.</div>
</div>

<!-- Preview area -->
<div id="payment_proof_preview" class="mt-3 d-none">
  <img id="preview_image" class="img-thumbnail" style="max-height: 200px;" />
  <a href="#" id="preview_pdf" class="d-none">
    <i class="ki-duotone ki-document fs-2"><span class="path1"></span><span class="path2"></span></i>
    Lihat PDF
  </a>
</div>
```

### 11.7 Rejection Reason Textarea

```html
<div class="fv-row mb-7">
  <label class="fs-6 fw-semibold form-label mt-3">
    <span class="required">Alasan Penolakan</span>
  </label>
  <textarea class="form-control form-control-solid" rows="3"
            name="rejection_reason" placeholder="Jelaskan alasan penolakan..."></textarea>
  <div class="form-text">Alasan akan dikirimkan ke pelanggan.</div>
</div>
```

### 11.8 Currency Input (Package Price)

```html
<div class="fv-row mb-7">
  <label class="fs-6 fw-semibold form-label mt-3">
    <span class="required">Harga Bulanan</span>
  </label>
  <div class="input-group">
    <span class="input-group-text">Rp</span>
    <input type="text" class="form-control form-control-solid"
           name="price" placeholder="150000"
           data-inputmask="'mask': '999.999.999', 'removeMaskOnSubmit': true"
           id="price_input" />
  </div>
</div>
```

---

## 12. Tables

### 12.1 Customer Table

```html
<table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_customers_table">
  <thead>
    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
      <th class="w-10px pe-2">
        <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
          <input class="form-check-input" type="checkbox" data-kt-check="true"
                 data-kt-check-target="#kt_customers_table .form-check-input" value="1" />
        </div>
      </th>
      <th class="min-w-150px">Pelanggan</th>
      <th class="min-w-150px">Paket</th>
      <th class="min-w-100px">Status</th>
      <th class="min-w-100px">Kontak</th>
      <th class="text-end min-w-100px">Aksi</th>
    </tr>
  </thead>
  <tbody class="fw-semibold text-gray-700">
    <!-- Data filled by DataTable JS -->
  </tbody>
</table>
```

### 12.2 Invoice Table

```html
<table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_invoices_table">
  <thead>
    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
      <th class="w-10px pe-2">
        <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
          <input class="form-check-input" type="checkbox" data-kt-check="true"
                 data-kt-check-target="#kt_invoices_table .form-check-input" value="1" />
        </div>
      </th>
      <th class="min-w-125px">No. Invoice</th>
      <th class="min-w-150px">Pelanggan</th>
      <th class="min-w-125px">Periode</th>
      <th class="min-w-100px">Jatuh Tempo</th>
      <th class="min-w-125px text-end">Nominal</th>
      <th class="min-w-100px">Status</th>
      <th class="text-end min-w-100px">Aksi</th>
    </tr>
  </thead>
  <tbody class="fw-semibold text-gray-700"></tbody>
</table>
```

### 12.3 Verification Table

```html
<table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_verifications_table">
  <thead>
    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
      <th class="w-10px pe-2">
        <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
          <input class="form-check-input" type="checkbox" data-kt-check="true"
                 data-kt-check-target="#kt_verifications_table .form-check-input" value="1" />
        </div>
      </th>
      <th class="min-w-150px">Invoice</th>
      <th class="min-w-150px">Pelanggan</th>
      <th class="min-w-100px">Nominal</th>
      <th class="min-w-150px">Bukti Bayar</th>
      <th class="min-w-150px">Waktu Upload</th>
      <th class="text-end min-w-100px">Aksi</th>
    </tr>
  </thead>
  <tbody class="fw-semibold text-gray-700"></tbody>
</table>
```

---

## 13. DataTables Server-Side (Standar K2-Net)

> **Penting:** Semua request DataTable dari Blade page harus menyertakan CSRF token.
> JWT token tidak dipakai di sini — Blade menggunakan session-based auth untuk interaksi server-side.

### 13.1 Arsitektur

```
Browser (DataTables) ←→ Controller::datatable() ←→ Eloquent Query
                              ↓
                        response()->json([
                          draw, recordsTotal, recordsFiltered, data[]
                        ])
```

- Endpoint: `GET /api/v1/admin/{module}/datatable` → route name: `api.v1.admin.{module}.datatable`
- Controller method: `datatable(Request $request)` → `response()->json(...)`
- Server-side processing: search, sort, paginate dilakukan DI BACKEND
- JS hanya render response + apply custom filters via `data` callback

### 13.2 Controller Pattern

```php
public function datatable(Request $request): JsonResponse
{
    $recordsTotal = Invoice::count();

    $search = trim((string) $request->input('search.value', ''));
    $status = $request->input('filter.status', '');

    $query = Invoice::query()
        ->with(['customer', 'customer.package']);

    if ($search !== '') {
        $query->where(function ($q) use ($search) {
            $q->whereRaw('LOWER(invoice_number) LIKE ?', ['%' . strtolower($search) . '%'])
              ->orWhereHas('customer', fn($cq) => $cq->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%']));
        });
    }

    if ($status !== '') {
        $query->where('status', $status);
    }

    $recordsFiltered = $query->count();

    $orderCol = $request->input('order.0.column');
    $orderDir = $request->input('order.0.dir', 'asc');

    $start  = max(0, (int) $request->input('start', 0));
    $length = min(100, max(1, (int) $request->input('length', 10)));
    $rows   = $query->skip($start)->take($length)->get();

    $data = $rows->map(fn ($r) => [
        'id'             => $r->id,
        'invoice_number' => $r->invoice_number,
        'customer_name'  => $r->customer->name,
        'package_name'   => $r->customer->package->name ?? '-',
        'period'         => $r->billing_period->format('F Y'),
        'due_date'       => $r->due_date->format('d M Y'),
        'amount'         => 'Rp' . number_format($r->amount, 0, ',', '.'),
        'amount_raw'     => $r->amount,
        'status'         => $r->status,
        'detail_url'     => route('admin.invoices.show', $r->id),
    ]);

    return response()->json([
        'draw'            => (int) $request->input('draw', 1),
        'recordsTotal'    => $recordsTotal,
        'recordsFiltered' => $recordsFiltered,
        'data'            => $data,
    ]);
}
```

### 13.3 View — Blade Template (Invoice List)

```html
<div class="card card-flush">
    <!-- Card Header: search + filters -->
    <div class="card-header pt-5 pb-3">
        <div class="card-title w-100 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <!-- Search -->
            <div class="d-flex align-items-center position-relative my-1">
                <i class="ki-duotone ki-magnifier fs-1 position-absolute text-muted"
                   style="z-index:1; left: 12px; top: 50%; transform: translateY(-50%);"></i>
                <input type="text"
                       data-kt-invoices-table-filter="search"
                       class="form-control form-control-solid w-250px ps-12 pe-5"
                       placeholder="Cari invoice..." />
            </div>
            <!-- Status Filter -->
            <div class="d-flex align-items-center gap-3">
                <select data-kt-invoices-table-filter="status" class="form-select form-select-solid fw-bold w-180px">
                    <option value="">Semua Status</option>
                    <option value="belum_bayar">Belum Bayar</option>
                    <option value="menunggu_verifikasi">Menunggu Verifikasi</option>
                    <option value="lunas">Lunas</option>
                    <option value="ditolak">Ditolak</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Card Body: Table -->
    <div class="card-body pt-0">
        <table id="kt_invoices_table" class="table align-middle table-row-dashed table-row-gray-300 fs-6 gy-5">
            <thead>
                <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                    <th class="w-10px">#</th>
                    <th class="min-w-125px">No. Invoice</th>
                    <th class="min-w-150px">Pelanggan</th>
                    <th class="min-w-125px">Periode</th>
                    <th class="min-w-100px">Jatuh Tempo</th>
                    <th class="min-w-125px text-end">Nominal</th>
                    <th class="min-w-100px">Status</th>
                    <th class="text-end min-w-100px">Aksi</th>
                </tr>
            </thead>
            <tbody class="fw-semibold text-gray-700"></tbody>
        </table>

        <!-- Pagination wrappers -->
        <div id="kt_invoices_pagination" class="d-flex justify-content-between align-items-center flex-wrap mt-5 gap-3">
            <div class="d-flex align-items-center gap-3" id="kt_invoices_length"></div>
            <div class="d-flex align-items-center" id="kt_invoices_info"></div>
            <div id="kt_invoices_paginate"></div>
        </div>
    </div>
</div>
```

### 13.4 JavaScript — DataTable Init (Invoice)

```javascript
(function () {
    "use strict";

    var dt;
    var statusBadge = {
        'belum_bayar': '<span class="badge badge-light-danger">Belum Bayar</span>',
        'menunggu_verifikasi': '<span class="badge badge-light-warning">Menunggu Verifikasi</span>',
        'lunas': '<span class="badge badge-light-success">Lunas</span>',
        'ditolak': '<span class="badge badge-light-dark">Ditolak</span>',
    };

    var renderStatus = function (status) {
        return statusBadge[status] || status;
    };

    var renderActions = function (row) {
        return '<a href="' + row.detail_url + '" class="btn btn-sm btn-light-primary me-2">' +
               '<i class="ki-duotone ki-eye fs-2"><span class="path1"></span><span class="path2"></span></i>' +
               '</a>';
    };

    var drawCallback = function (settings) {
        var lengthEl = document.querySelector('.dataTables_length');
        var infoEl   = document.querySelector('.dataTables_info');
        var pageEl   = document.querySelector('.dataTables_paginate');

        if (lengthEl) {
            var wrap = document.getElementById('kt_invoices_length');
            var select = lengthEl.querySelector('select');
            if (select) { select.className = 'form-select form-select-sm form-select-solid w-auto'; }
            if (wrap) { wrap.innerHTML = lengthEl.outerHTML; lengthEl.remove(); }
        }
        if (infoEl) {
            var infoWrap = document.getElementById('kt_invoices_info');
            if (infoWrap) { infoWrap.innerHTML = infoEl.outerHTML; infoEl.remove(); }
        }
        if (pageEl) {
            var pageWrap = document.getElementById('kt_invoices_paginate');
            if (pageWrap) { pageWrap.innerHTML = pageEl.outerHTML; pageEl.remove(); }
        }
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
            if (!el._tooltip) { new bootstrap.Tooltip(el); }
        });
    };

    dt = jQuery('#kt_invoices_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('api.v1.admin.invoices.datatable') }}",
            type: 'GET',
            data: function (d) {
                d.filter = {
                    status: document.querySelector('[data-kt-invoices-table-filter="status"]').value || ''
                };
            }
        },
        columns: [
            { data: null, orderable: false, searchable: false },
            { data: 'invoice_number', name: 'invoice_number', orderable: true, searchable: true },
            { data: 'customer_name',  name: 'customer_name',  orderable: true, searchable: false },
            { data: 'period',         name: 'period',         orderable: true, searchable: false },
            { data: 'due_date',      name: 'due_date',       orderable: true, searchable: false },
            { data: 'amount',         name: 'amount',         orderable: true, searchable: false, className: 'text-end' },
            { data: 'status',         name: 'status',         orderable: true, searchable: false },
            { data: 'detail_url',     name: 'detail_url',     orderable: false, searchable: false, className: 'text-end' }
        ],
        columnDefs: [
            { targets: 0, render: function (data, type, row, meta) { return meta.row + meta.settings._iDisplayStart + 1; } },
            { targets: 6, render: function (data) { return renderStatus(data); } },
            { targets: 7, render: function (data, type, row) { return renderActions(row); } }
        ],
        drawCallback: drawCallback,
        order: [],
        searchDelay: 400,
        pagingType: 'simple_numbers',
        pageLength: 10,
        lengthChange: true,
        lengthMenu: [5, 10, 20, 50, 100],
        language: {
            processing: '<span class="spinner-border spinner-border-sm align-middle text-primary me-2"></span> Memuat...',
            lengthMenu: 'Tampilkan _MENU_',
            zeroRecords: 'Tidak ada data yang cocok',
            info: 'Menampilkan _START_–_END_ dari _TOTAL_ data',
            infoEmpty: 'Menampilkan 0 dari 0 data',
            infoFiltered: '(disaring dari _MAX_ total)',
            search: '',
            paginate: {
                previous: '<i class="ki-duotone ki-left fs-3"><span class="path1"></span><span class="path2"></span></i>',
                next:     '<i class="ki-duotone ki-right fs-3"><span class="path1"></span><span class="path2"></span></i>'
            }
        },
        dom: "<'row'<'col-sm-12'tr>>" +
             "<'row mt-5'<'col-sm-12 col-md-5 d-flex align-items-center justify-content-center justify-content-md-start'l>" +
             "<'col-sm-12 col-md-7 d-flex align-items-center justify-content-center justify-content-md-end'p>>"
    });

    document.querySelector('[data-kt-invoices-table-filter="search"]')
        .addEventListener('keyup', function (e) {
            dt.search(e.target.value).draw();
        });

    document.querySelector('[data-kt-invoices-table-filter="status"]')
        .addEventListener('change', function () { dt.draw(); });
})();
```

### 13.5 Route Setup

```php
// routes/api.php
Route::prefix('v1')->group(function () {
    Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
        Route::get('{module}/datatable', [ModuleController::class, 'datatable'])
            ->name('api.v1.admin.{module}.datatable');
    });
});

// routes/web.php (untuk Blade page)
Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::get('{module}', [ModuleController::class, 'index'])->name('admin.{module}.index');
    Route::get('{module}/{id}', [ModuleController::class, 'show'])->name('admin.{module}.show');
});
```

---

## 14. Modals

### 14.1 Reject Payment Modal

```html
<div class="modal fade" id="kt_modal_reject" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title">Tolak Pembayaran</h3>
        <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal">
          <i class="ki-duotone ki-cross fs-1">
            <span class="path1"></span><span class="path2"></span>
          </i>
        </div>
      </div>
      <form method="POST" action="" id="reject_form">
        @csrf
        <div class="modal-body">
          <input type="hidden" name="payment_proof_id" id="reject_payment_proof_id" />
          <div class="fv-row mb-7">
            <label class="fs-6 fw-semibold form-label">
              <span class="required">Alasan Penolakan</span>
            </label>
            <textarea class="form-control form-control-solid" rows="3"
                      name="rejection_reason" id="rejection_reason"
                      placeholder="Jelaskan alasan penolakan..." required></textarea>
            <div class="form-text">Alasan akan dikirimkan ke pelanggan.</div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-danger">
            <i class="ki-duotone ki-cross-circle fs-2"><span class="path1"></span><span class="path2"></span></i>
            Tolak Pembayaran
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
```

### 14.2 View Payment Proof Modal

```html
<div class="modal fade" id="kt_modal_view_proof" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title">Bukti Pembayaran</h3>
        <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal">
          <i class="ki-duotone ki-cross fs-1">
            <span class="path1"></span><span class="path2"></span>
          </i>
        </div>
      </div>
      <div class="modal-body">
        <img id="proof_image" class="img-fluid rounded" style="max-height: 500px;" />
        <iframe id="proof_pdf" class="d-none" style="width: 100%; height: 500px;"></iframe>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>
```

### 14.3 Customer Detail Modal (Quick View)

```html
<div class="modal fade" id="kt_modal_customer_detail" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title">Detail Pelanggan</h3>
        <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal">
          <i class="ki-duotone ki-cross fs-1">
            <span class="path1"></span><span class="path2"></span>
          </i>
        </div>
      </div>
      <div class="modal-body" id="customer_detail_content">
        <!-- Filled via AJAX -->
      </div>
    </div>
  </div>
</div>
```

### 14.4 Open Modal via Button

```html
<!-- Approve -->
<form method="POST" action="" class="d-inline js-swal-confirm-form"
      data-swal-text="Setujui pembayaran ini? Status akan berubah menjadi Lunas.">
  @csrf
  <button type="submit" class="btn btn-sm btn-success">
    <i class="ki-duotone ki-check fs-2"><span class="path1"></span><span class="path2"></span></i>
    Approve
  </button>
</form>

<!-- Reject -->
<button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
        data-bs-target="#kt_modal_reject"
        data-payment-proof-id="" onclick="openRejectModal(this)">
  <i class="ki-duotone ki-cross-circle fs-2"><span class="path1"></span><span class="path2"></span></i>
  Tolak
</button>

<!-- View Proof -->
<button type="button" class="btn btn-sm btn-light-primary"
        data-bs-toggle="modal" data-bs-target="#kt_modal_view_proof"
        data-proof-url="" onclick="openProofModal(this)">
  <i class="ki-duotone ki-eye fs-2"><span class="path1"></span><span class="path2"></span></i>
  Lihat Bukti
</button>
```

---

## 15. Tabs & Pills

### 15.1 Tab Nav (Invoice Filter)

```html
<ul class="nav nav-tabs mb-5" id="kt_invoice_tabs" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" id="tab-all" data-bs-toggle="tab"
            data-bs-target="#pane-all" type="button">Semua</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="tab-unpaid" data-bs-toggle="tab"
            data-bs-target="#pane-unpaid" type="button">Belum Bayar</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="tab-paid" data-bs-toggle="tab"
            data-bs-target="#pane-paid" type="button">Lunas</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="tab-overdue" data-bs-toggle="tab"
            data-bs-target="#pane-overdue" type="button">Jatuh Tempo</button>
  </li>
</ul>
```

---

## 16. Avatar & Symbol

### 16.1 Customer Avatar in Table

```html
<tr>
  <td>
    <div class="d-flex align-items-center">
      <div class="symbol symbol-50px me-3">
        <span class="symbol-label bg-primary text-inverse-primary fw-bold fs-5">
          {{ Str::initials($customer->name) }}
        </span>
      </div>
      <div class="d-flex justify-content-start flex-column">
        <a href="#" class="text-gray-900 fw-bold text-hover-primary mb-1 fs-6">
          {{ $customer->name }}
        </a>
        <span class="text-muted fw-semibold d-block fs-7">
          {{ $customer->whatsapp_number }}
        </span>
      </div>
    </div>
  </td>
</tr>
```

---

## 17. Alerts & Notifications

### 17.1 Success Alert

```html
<div class="alert alert-success d-flex align-items-center p-5 mb-10">
  <i class="ki-duotone ki-check-circle fs-2 me-4 text-success">
    <span class="path1"></span><span class="path2"></span>
  </i>
  <div class="d-flex flex-column">
    <h4 class="mb-1 text-success">Berhasil!</h4>
    <span>Invoice berhasil dibuat untuk seluruh pelanggan aktif.</span>
  </div>
</div>
```

### 17.2 Error Alert

```html
<div class="alert alert-danger d-flex align-items-center p-5">
  <i class="ki-duotone ki-cross-circle fs-2 me-4 text-danger">
    <span class="path1"></span><span class="path2"></span>
  </i>
  <div class="d-flex flex-column">
    <h4 class="mb-1 text-danger">Gagal!</h4>
    <span>{{ $errors->first() }}</span>
  </div>
</div>
```

### 17.3 Warning Notice

```html
<div class="notice d-flex bg-light-warning rounded border-warning border border-dashed p-6 mb-10">
  <div class="d-flex flex-stack flex-grow-1">
    <div class="fw-semibold">
      <h4 class="text-gray-900 fw-bolder">Perhatian</h4>
      <div class="fs-6 text-gray-700">Pastikan bukti transfer sesuai dengan nominal tagihan.</div>
    </div>
  </div>
</div>
```

### 17.4 Toast Notification (SweetAlert2)

```javascript
// Success toast
Swal.fire({
    text: "Invoice berhasil dibuat!",
    icon: "success",
    buttonsStyling: false,
    confirmButtonText: "OK",
    customClass: { confirmButton: "btn btn-primary" }
});

// Error toast
Swal.fire({
    text: "Terjadi kesalahan. Silakan coba lagi.",
    icon: "error",
    buttonsStyling: false,
    confirmButtonText: "OK",
    customClass: { confirmButton: "btn btn-danger" }
});

// Confirmation
Swal.fire({
    text: "Setujui pembayaran ini?",
    icon: "warning",
    buttonsStyling: false,
    showCancelButton: true,
    confirmButtonText: "Ya, setujui",
    cancelButtonText: "Batal",
    customClass: {
        confirmButton: "btn btn-success",
        cancelButton: "btn btn-light"
    }
}).then(function (result) {
    if (result.isConfirmed) {
        form.submit();
    }
});
```

---

## 18. Pagination

### 18.1 Laravel Paginator Override

Setup di `AppServiceProvider`:

```php
use Illuminate\Pagination\Paginator;

public function boot(): void
{
    Paginator::defaultView('vendor.pagination.metronic');
    Paginator::defaultSimpleView('vendor.pagination.metronic-simple');
}
```

### 18.2 Metronic Pagination Blade

File: `resources/views/vendor/pagination/metronic.blade.php`

```blade
@if ($paginator->hasPages())
<nav class="d-flex flex-stack flex-wrap" aria-label="Pagination">
    <div class="d-flex align-items-center text-gray-700 fw-semibold fs-7 me-2">
        Menampilkan {{ $paginator->firstItem() ?? 0 }}–{{ $paginator->lastItem() ?? 0 }}
        dari {{ $paginator->total() }} data
    </div>

    <ul class="pagination pagination-outline">
        @if ($paginator->onFirstPage())
            <li class="page-item previous disabled" aria-disabled="true">
                <span class="page-link">
                    <i class="ki-duotone ki-left fs-3"><span class="path1"></span><span class="path2"></span></i>
                </span>
            </li>
        @else
            <li class="page-item previous">
                <a href="{{ $paginator->previousPageUrl() }}" class="page-link" rel="prev">
                    <i class="ki-duotone ki-left fs-3"><span class="path1"></span><span class="path2"></span></i>
                </a>
            </li>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link">{{ $element }}</span>
                </li>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li class="page-item active" aria-current="page">
                            <span class="page-link">{{ $page }}</span>
                        </li>
                    @else
                        <li class="page-item">
                            <a href="{{ $url }}" class="page-link">{{ $page }}</a>
                        </li>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <li class="page-item next">
                <a href="{{ $paginator->nextPageUrl() }}" class="page-link" rel="next">
                    <i class="ki-duotone ki-right fs-3"><span class="path1"></span><span class="path2"></span></i>
                </a>
            </li>
        @else
            <li class="page-item next disabled" aria-disabled="true">
                <span class="page-link">
                    <i class="ki-duotone ki-right fs-3"><span class="path1"></span><span class="path2"></span></i>
                </span>
            </li>
        @endif
    </ul>
</nav>
@endif
```

---

## 19. Customer Portal (Pelanggan) Specific

### 19.1 Portal Header

```html
<div id="kt_app_header" class="app-header" data-kt-sticky="true"
     data-kt-sticky-activate="{default: true, lg: true}"
     data-kt-sticky-name="app-header-minimize"
     data-kt-sticky-offset="{default: '200px', lg: '0'}">
  <div class="app-container container-fluid d-flex align-items-stretch justify-content-between"
       id="kt_app_header_container">
    <!-- Logo -->
    <a href="{{ route('portal.dashboard') }}" class="d-flex align-items-center">
      <img alt="Logo" src="{{ asset('assets/media/logos/default-dark.svg') }}" class="h-25px" />
      <span class="fs-4 fw-bold text-gray-800 ms-2">K2-Net</span>
    </a>

    <!-- Portal Navbar -->
    <div class="app-navbar flex-shrink-0">
      <div class="app-navbar-item">
        <a href="{{ route('portal.dashboard') }}"
           class="btn btn-sm {{ request()->routeIs('portal.dashboard') ? 'btn-primary' : 'btn-light' }} me-2">
          Tagihan
        </a>
        <a href="{{ route('portal.history') }}"
           class="btn btn-sm {{ request()->routeIs('portal.history') ? 'btn-primary' : 'btn-light' }} me-2">
          Riwayat
        </a>
      </div>

      <!-- User Menu -->
      <div class="app-navbar-item ms-1 ms-md-4" id="kt_header_user_menu_toggle">
        <div class="cursor-pointer symbol symbol-35px"
             data-kt-menu-trigger="{default: 'click', lg: 'hover'}"
             data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">
          <span class="symbol-label bg-primary text-inverse-primary fw-bold">
            {{ Str::initials(Auth::user()->name) }}
          </span>
        </div>
        <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-color fw-semibold py-4 fs-6 w-275px" data-kt-menu="true">
          <div class="menu-item px-5">
            <div class="fw-bold">{{ Auth::user()->name }}</div>
            <div class="text-muted fs-7">{{ Auth::user()->email }}</div>
          </div>
          <div class="separator my-2"></div>
          <div class="menu-item px-5">
            <form method="POST" action="{{ route('portal.logout') }}">
              @csrf
              <button type="submit" class="btn btn-sm btn-danger w-100">Logout</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
```

### 19.2 Current Invoice Card (Portal)

```html
<div class="card card-flush mb-5">
  <div class="card-header pt-5">
    <h3 class="card-title align-items-start flex-column">
      <span class="card-label fw-bold text-gray-900">Tagihan Bulan Ini</span>
      <span class="text-gray-500 mt-1 fw-semibold fs-6">{{ $currentInvoice->billing_period->format('F Y') }}</span>
    </h3>
    <span class="badge badge-light-{{ $statusColor }}">{{ $currentInvoice->status_label }}</span>
  </div>
  <div class="card-body pt-5">
    <div class="d-flex flex-stack">
      <div>
        <div class="fs-6 fw-semibold text-gray-500">Nominal Tagihan</div>
        <div class="fs-2hx fw-bold text-gray-900">
          Rp{{ number_format($currentInvoice->amount, 0, ',', '.') }}
        </div>
      </div>
      <div class="text-end">
        <div class="fs-6 fw-semibold text-gray-500">Jatuh Tempo</div>
        <div class="fs-2hx fw-bold text-{{ $overdue ? 'danger' : 'gray-900' }}">
          {{ $currentInvoice->due_date->format('d M Y') }}
        </div>
        @if($overdue)
          <span class="badge badge-light-danger mt-1">Terlambat {{ $daysOverdue }} hari</span>
        @endif
      </div>
    </div>

    @if($currentInvoice->status === 'belum_bayar')
      <div class="separator my-5"></div>
      <div class="d-flex flex-stack">
        <div class="fs-6 fw-semibold text-gray-500">Transfer ke:</div>
      </div>
      <div class="alert alert-secondary d-flex align-items-center mt-2">
        <div>
          <div class="fw-bold">Bank BCA</div>
          <div class="fs-5 fw-bold">1234567890 a.n. K2-Net</div>
        </div>
      </div>
      <div class="d-grid mt-3">
        <a href="{{ route('portal.invoices.upload', $currentInvoice->id) }}"
           class="btn btn-primary btn-lg">
          <i class="ki-duotone ki-upload fs-2"><span class="path1"></span><span class="path2"></span></i>
          Upload Bukti Bayar
        </a>
      </div>
    @elseif($currentInvoice->status === 'menunggu_verifikasi')
      <div class="alert alert-warning d-flex align-items-center mt-5">
        <i class="ki-duotone ki-information-5 fs-2 me-4 text-warning">
          <span class="path1"></span><span class="path2"></span><span class="path3"></span>
        </i>
        <span>Pembayaran sedang dalam proses verifikasi oleh admin.</span>
      </div>
    @elseif($currentInvoice->status === 'lunas')
      <div class="alert alert-success d-flex align-items-center mt-5">
        <i class="ki-duotone ki-check-circle fs-2 me-4 text-success">
          <span class="path1"></span><span class="path2"></span>
        </i>
        <span>Pembayaran sudah lunas pada {{ $currentInvoice->paid_at->format('d M Y') }}.</span>
      </div>
    @elseif($currentInvoice->status === 'ditolak')
      <div class="alert alert-danger d-flex align-items-center mt-5">
        <i class="ki-duotone ki-cross-circle fs-2 me-4 text-danger">
          <span class="path1"></span><span class="path2"></span>
        </i>
        <div>
          <span>Pembayaran ditolak. Alasan: {{ $currentInvoice->rejection_reason }}</span>
          <a href="{{ route('portal.invoices.upload', $currentInvoice->id) }}"
             class="btn btn-sm btn-danger mt-2">Upload Ulang</a>
        </div>
      </div>
    @endif
  </div>
</div>
```

---

## 20. Authentication Pages

### 20.1 Admin Login Page

```html
<html data-bs-theme="light">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>Login — K2-Net Admin</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
</head>

<body id="kt_body" class="app-blank bgi-size-cover bgi-position-center bgi-no-repeat">
  <style>
    body { background-image: url('{{ asset('assets/media/auth/bg1.jpg') }}'); }
    [data-bs-theme="dark"] body { background-image: url('{{ asset('assets/media/auth/bg1-dark.jpg') }}'); }
  </style>

  <div class="d-flex flex-column flex-root">
    <div class="d-flex flex-column flex-center flex-column-fluid">
      <div class="d-flex flex-column flex-center text-center p-10">
        <div class="card card-flush w-lg-500px py-5">
          <div class="card-body py-15 py-lg-20">
            <!-- Logo -->
            <div class="mb-5">
              <img alt="Logo" src="{{ asset('assets/media/logos/default-dark.svg') }}"
                   class="h-40px mb-5" />
              <h1 class="fw-bolder text-gray-900 mb-2">K2-Net Admin</h1>
              <p class="text-gray-500 fw-semibold">Sistem Manajemen Tagihan & Pelanggan</p>
            </div>

            <!-- Form -->
            <form class="w-100" method="POST" action="{{ route('login') }}">
              @csrf
              <div class="fv-row mb-7">
                <label class="form-label fs-6 fw-semibold required">Email</label>
                <input type="email" class="form-control form-control-lg form-control-solid"
                       name="email" autocomplete="email" value="{{ old('email') }}" required autofocus />
                @error('email')
                  <div class="fv-plugins-message-container invalid-feedback">
                    <div>{{ $message }}</div>
                  </div>
                @enderror
              </div>
              <div class="fv-row mb-7">
                <label class="form-label fs-6 fw-semibold required">Password</label>
                <input type="password" class="form-control form-control-lg form-control-solid"
                       name="password" autocomplete="current-password" required />
                @error('password')
                  <div class="fv-plugins-message-container invalid-feedback">
                    <div>{{ $message }}</div>
                  </div>
                @enderror
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
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>var hostUrl = "{{ asset('assets/') }}";</script>
  <script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
  <script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>
</body>
</html>
```

### 20.2 Pelanggan Portal Login Page

Sama seperti admin login, tapi dengan branding dan link "Login sebagai Pelanggan":

```html
<!-- Logo -->
<h1 class="fw-bolder text-gray-900 mb-2">Portal Pelanggan K2-Net</h1>
<p class="text-gray-500 fw-semibold">Masuk untuk melihat tagihan dan riwayat pembayaran</p>

<!-- Form fields sama, tapi action: route('portal.login') -->
<!-- Link ke admin login: route('login') -->
```

---

## 21. Export Report Cards

```html
<div class="card card-flush mb-5">
  <div class="card-header pt-5">
    <h3 class="card-title align-items-start flex-column">
      <span class="card-label fw-bold text-gray-900">Ekspor Laporan</span>
    </h3>
  </div>
  <div class="card-body">
    <form method="GET" action="{{ route('admin.reports.export') }}">
      <div class="row g-5 mb-5">
        <div class="col-md-4">
          <label class="form-label">Jenis Laporan</label>
          <select class="form-select form-select-solid" name="type" data-control="select2">
            <option value="invoices">Daftar Tagihan</option>
            <option value="payments">Daftar Pembayaran</option>
            <option value="customers">Daftar Pelanggan</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Tanggal Mulai</label>
          <input type="date" class="form-control form-control-solid" name="start_date" />
        </div>
        <div class="col-md-3">
          <label class="form-label">Tanggal Akhir</label>
          <input type="date" class="form-control form-control-solid" name="end_date" />
        </div>
        <div class="col-md-2 d-flex align-items-end">
          <button type="submit" class="btn btn-light-primary w-100">
            <i class="ki-duotone ki-arrows-loop fs-2"><span class="path1"></span><span class="path2"></span></i>
            Export
          </button>
        </div>
      </div>
    </form>

    <div class="row g-5">
      <div class="col-md-4">
        <div class="d-flex align-items-center gap-3 p-4 bg-light rounded">
          <i class="ki-duotone ki-document fs-2tx text-primary"></i>
          <div>
            <div class="fs-6 fw-bold">Format XLSX</div>
            <div class="fs-7 text-muted">Microsoft Excel</div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="d-flex align-items-center gap-3 p-4 bg-light rounded">
          <i class="ki-duotone ki-doc-text fs-2tx text-success"></i>
          <div>
            <div class="fs-6 fw-bold">Format CSV</div>
            <div class="fs-7 text-muted">Comma-separated values</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
```

---

## 22. Charts Widgets

### 22.1 Revenue Chart Card

```html
<div class="card card-flush h-xl-100">
  <div class="card-header pt-5">
    <h3 class="card-title align-items-start flex-column">
      <span class="card-label fw-bold text-gray-900">Pendapatan per Bulan</span>
      <span class="text-gray-500 mt-1 fw-semibold fs-6">Tahun {{ date('Y') }}</span>
    </h3>
  </div>
  <div class="card-body pt-6">
    <div id="revenue_chart" style="height: 275px"></div>
  </div>
</div>
```

### 22.2 Customer Status Distribution (Donut)

```html
<div class="card card-flush h-xl-100">
  <div class="card-header pt-5">
    <h3 class="card-title align-items-start flex-column">
      <span class="card-label fw-bold text-gray-900">Distribusi Status Pelanggan</span>
    </h3>
  </div>
  <div class="card-body pt-6">
    <div class="row">
      <div class="col-4">
        <div id="customer_chart" style="min-width: 100px; min-height: 100px"></div>
      </div>
      <div class="col-8 d-flex flex-column justify-content-center">
        <div class="d-flex align-items-center mb-3">
          <span class="bullet bullet-dot bg-success me-2 h-8px w-8px"></span>
          <span class="fw-semibold text-gray-600 fs-6">Aktif: {{ $stats['active_count'] }}</span>
        </div>
        <div class="d-flex align-items-center mb-3">
          <span class="bullet bullet-dot bg-warning me-2 h-8px w-8px"></span>
          <span class="fw-semibold text-gray-600 fs-6">Isolir: {{ $stats['isolir_count'] }}</span>
        </div>
        <div class="d-flex align-items-center">
          <span class="bullet bullet-dot bg-dark me-2 h-8px w-8px"></span>
          <span class="fw-semibold text-gray-600 fs-6">Nonaktif: {{ $stats['nonaktif_count'] }}</span>
        </div>
      </div>
    </div>
  </div>
</div>
```

---

## 23. Footer

### 23.1 App Footer (Admin)

```html
<div id="kt_app_footer" class="app-footer py-3 d-flex flex-column flex-md-row flex-center flex-md-stack">
  <div class="text-gray-700 order-2 order-md-1 w-100 text-center">
    <span class="text-muted fw-semibold me-1">&copy; {{ date('Y') }}</span>
    <a href="#" class="text-gray-600 fw-semibold text-hover-primary">K2-Net</a>
    <span class="text-muted fw-semibold me-1">— Sistem Manajemen Tagihan & Pelanggan</span>
  </div>
</div>
```

---

## 24. SweetAlert2 — Confirmation Patterns

### 24.1 Confirm Approve Payment

```javascript
// Attach to form dengan class js-swal-confirm-form
document.addEventListener('submit', function (e) {
    if (!e.target.classList.contains('js-swal-confirm-form')) return;
    e.preventDefault();

    var form = e.target;
    Swal.fire({
        text: form.getAttribute('data-swal-text') || 'Setujui pembayaran ini?',
        icon: "warning",
        buttonsStyling: false,
        showCancelButton: true,
        confirmButtonText: "Ya, setujui",
        cancelButtonText: "Batal",
        customClass: {
            confirmButton: "btn btn-success",
            cancelButton: "btn btn-light"
        }
    }).then(function (result) {
        if (!result.isConfirmed) return;
        form.submit();
    });
});
```

### 24.2 Confirm Reject (with reason modal)

```javascript
function openRejectModal(btn) {
    var proofId = btn.getAttribute('data-payment-proof-id');
    var formAction = btn.getAttribute('data-action');

    document.getElementById('reject_payment_proof_id').value = proofId;
    document.getElementById('reject_form').action = formAction;

    var modal = new bootstrap.Modal(document.getElementById('kt_modal_reject'));
    modal.show();
}

document.getElementById('reject_form').addEventListener('submit', function (e) {
    e.preventDefault();
    var form = this;
    var reason = document.getElementById('rejection_reason').value;

    if (!reason.trim()) {
        Swal.fire({ text: 'Alasan penolakan wajib diisi.', icon: 'error', buttonsStyling: false,
            confirmButtonText: 'OK', customClass: { confirmButton: 'btn btn-danger' }
        });
        return;
    }

    Swal.fire({
        text: 'Tolak pembayaran ini?',
        icon: "warning",
        buttonsStyling: false,
        showCancelButton: true,
        confirmButtonText: "Ya, tolak",
        cancelButtonText: "Batal",
        customClass: {
            confirmButton: "btn btn-danger",
            cancelButton: "btn btn-light"
        }
    }).then(function (result) {
        if (!result.isConfirmed) return;
        form.submit();
    });
});
```

### 24.3 View Payment Proof Modal

```javascript
function openProofModal(btn) {
    var proofUrl = btn.getAttribute('data-proof-url');
    var proofType = btn.getAttribute('data-proof-type');

    var img = document.getElementById('proof_image');
    var pdf = document.getElementById('proof_pdf');

    if (proofType === 'pdf') {
        img.classList.add('d-none');
        pdf.classList.remove('d-none');
        pdf.src = proofUrl;
    } else {
        pdf.classList.add('d-none');
        img.classList.remove('d-none');
        img.src = proofUrl;
    }
}
```

---

## 25. Utility Classes

| Class | Description |
|-------|-------------|
| `fs-1` – `fs-7` | Font size |
| `fw-bold` / `fw-semibold` / `fw-medium` | Font weight |
| `text-primary` / `text-danger` / `text-muted` | Text color |
| `badge badge-light-{color}` | Badge dengan warna light |
| `badge badge-{color}` | Badge dengan warna solid |
| `d-flex` / `d-none` | Display flex/none |
| `align-items-center` / `justify-content-between` | Flex alignment |
| `gap-2` / `gap-5` | Gap spacing |
| `p-5` / `px-5` / `py-5` | Padding |
| `mb-5` / `mt-3` | Margin |
| `rounded` / `rounded-3` | Border radius |
| `text-nowrap` | No wrap |
| `w-100` / `w-50` | Width |
| `min-w-*` / `max-w-*` | Min/max width |

---

## 26. Icon System (Keenicons Duotone)

### Icon yang Sering Dipakai di K2-Net

| Kegunaan | Icon Class |
|----------|------------|
| Dashboard | `ki-duotone ki-home` (multi) |
| Pelanggan | `ki-duotone ki-profile-circle` (multi) |
| Paket | `ki-duotone ki-package` (multi) |
| Invoice/Tagihan | `ki-duotone ki-document` (multi) |
| Approve/Check | `ki-duotone ki-check` (single) |
| Reject/Cross | `ki-duotone ki-cross-circle` (multi) |
| Eye (view) | `ki-duotone ki-eye` (multi) |
| Upload | `ki-duotone ki-upload` (multi) |
| Download/Export | `ki-duotone ki-arrows-loop` (multi) |
| Send (notif) | `ki-duotone ki-send` (multi) |
| Gear (settings) | `ki-duotone ki-gear` (multi) |
| Chart | `ki-duotone ki-chart-line-up` (multi) |
| Plus (add) | `ki-duotone ki-plus` (multi) |
| Edit | `ki-duotone ki-pencil` (multi) |
| Trash | `ki-duotone ki-trash` (multi) |
| Calendar | `ki-duotone ki-calendar` (multi) |
| Phone/WhatsApp | `ki-duotone ki-phone` (multi) |
| Email | `ki-duotone ki-mail` (multi) |
| Warning | `ki-duotone ki-warning` (single) |
| Info | `ki-duotone ki-information-5` (single) |
| Money | `ki-duotone ki-wallet` (multi) |

> **KRITIS:** Icon multi-path WAJIB pakai `<span class="path1"></span>` dst.
> Icon single-path TANPA span path.

---

## 26. API Authentication (JWT) Patterns

> Untuk interaksi API dari Blade page yang butuh JSON response (misal: modal, AJAX tanpa reload), gunakan route yang mengembalikan JSON dengan CSRF token. Untuk mobile app atau integrasi pihak ketiga, gunakan JWT Bearer token.

### 26.1 Sending JWT Token (JavaScript)

```javascript
// Include JWT token di setiap request API
var jwtToken = localStorage.getItem('k2net_access_token');

fetch('/api/v1/admin/customers', {
    method: 'GET',
    headers: {
        'Authorization': 'Bearer ' + jwtToken,
        'Accept': 'application/json',
        'Content-Type': 'application/json'
    }
}).then(function(response) {
    if (response.status === 401) {
        // Token expired — try refresh
        return refreshToken().then(function(newToken) {
            localStorage.setItem('k2net_access_token', newToken);
            return fetch('/api/v1/admin/customers', {
                headers: {
                    'Authorization': 'Bearer ' + newToken,
                    'Accept': 'application/json'
                }
            });
        });
    }
    return response.json();
}).then(function(data) {
    console.log(data);
});
```

### 26.2 Refresh Token Flow

```javascript
function refreshToken() {
    var refreshToken = localStorage.getItem('k2net_refresh_token');
    return fetch('/api/v1/auth/refresh', {
        method: 'POST',
        headers: {
            'Authorization': 'Bearer ' + refreshToken,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    }).then(function(response) {
        if (!response.ok) {
            // Refresh failed — redirect to login
            localStorage.removeItem('k2net_access_token');
            localStorage.removeItem('k2net_refresh_token');
            window.location.href = '/login';
            return Promise.reject('Refresh failed');
        }
        return response.json();
    }).then(function(data) {
        return data.data.access_token;
    });
}
```

### 26.3 Login Form — API Pattern (untuk mobile/integrasi)

```javascript
function apiLogin(email, password) {
    return fetch('/api/v1/auth/login', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ email: email, password: password })
    }).then(function(response) {
        return response.json();
    }).then(function(data) {
        if (data.success) {
            localStorage.setItem('k2net_access_token', data.data.access_token);
            localStorage.setItem('k2net_refresh_token', data.data.refresh_token);
            localStorage.setItem('k2net_user', JSON.stringify(data.data.user));
        }
        return data;
    });
}
```

### 26.4 Logout Pattern

```javascript
function apiLogout() {
    var token = localStorage.getItem('k2net_access_token');
    fetch('/api/v1/auth/logout', {
        method: 'POST',
        headers: {
            'Authorization': 'Bearer ' + token,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    }).finally(function() {
        localStorage.removeItem('k2net_access_token');
        localStorage.removeItem('k2net_refresh_token');
        localStorage.removeItem('k2net_user');
        window.location.href = '/login';
    });
}
```

### 26.5 Error Handling — 401/403

```javascript
function handleApiError(error, action) {
    if (error.status === 401) {
        Swal.fire({
            text: 'Sesi Anda telah berakhir. Silakan login kembali.',
            icon: 'warning',
            confirmButtonText: 'OK'
        }).then(function() {
            localStorage.removeItem('k2net_access_token');
            localStorage.removeItem('k2net_refresh_token');
            window.location.href = '/login';
        });
        return;
    }

    if (error.status === 403) {
        Swal.fire({
            text: 'Anda tidak memiliki akses ke fitur ini.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
        return;
    }

    if (action === 'toast') {
        Swal.fire({
            text: 'Terjadi kesalahan. Silakan coba lagi.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    }
}
```

---

## 27. Notes Penting

1. **CDN Assets via `asset()` helper**: Gunakan `{{ asset('assets/...') }}` di Blade templates. Pastikan `.env` berisi `ASSET_URL=https://magang.skripsian.site`.

2. **Bootstrap 5**: Metronic 8.2.9 berbasis Bootstrap 5.

3. **CSRF Token**: Selalu gunakan `<meta name="csrf-token" content="{{ csrf_token() }}">` di head dan kirim via header AJAX.

4. **Select2**: Jangan lupa include `data-dropdown-parent` saat select berada di dalam modal agar dropdown tidak terpotong.

5. **Global Plugin JS**: Selalu load `plugins.bundle.js` sebelum `scripts.bundle.js`.

6. **DOM Structure — PERINGATAN KRITIS:**
   - Header di dalam `#kt_app_page`, SEBELUM `#kt_app_wrapper`
   - `#kt_app_wrapper` WAJIB — membungkus sidebar + main BERSAMA
   - Sidebar di dalam `#kt_app_wrapper`, SEBELUM `#kt_app_main`
   - Footer di dalam `#kt_app_main`, SETELAH `flex-column-fluid`

7. **Tailwind CSS — LARANGAN**: Jangan tambahkan `@tailwind base` di proyek Laravel yang menggunakan Metronic.

8. **Alpine.js — LARANGAN**: Jangan gunakan Alpine.js di proyek Laravel yang menggunakan Metronic.

9. **Vite**: Pendekatan ini TIDAK pakai Vite — langsung `<script src>` dan `<link href>`.

10. **CSS Loading Order**: Urutan load HARUS: (1) Google Fonts, (2) `plugins.bundle.css`, (3) `style.bundle.css`.

11. **SweetAlert2**: Sudah termasuk di `plugins.bundle.js`. Selalu gunakan `buttonsStyling: false` dan custom class dengan Metronic button classes.

12. **UUID v7**: Semua ID primary key menggunakan format `K2-{PREFIX}-{uuid7}`. Lihat `docs/AGENTS.md` Section 3.5b.

13. **Invoice Status**: 4 status — `belum_bayar`, `menunggu_verifikasi`, `lunas`, `ditolak`. State machine enforced.

14. **Customer Status**: 3 status — `aktif`, `isolir`, `nonaktif`. Pelanggan berstatus `isolir`/`nonaktif` tidak masuk proses generate invoice.

15. **JWT Authentication**: API menggunakan `tymon/jwt-auth` dengan access token (60 menit) dan refresh token (2 minggu). Web (Blade) tetap pakai session-based auth. Mobile app / integrasi pihak lain gunakan JWT Bearer token.

16. **Token Storage**: Frontend (mobile/web app) HARUS menyimpan `access_token` dan `refresh_token` di tempat yang aman (httpOnly cookie atau secure storage). Jangan simpan di localStorage untuk production web — pertimbangkan httpOnly cookie sebagai alternatif.

17. **Refresh Token**: Refresh token dikirim saat login bersama access token. Client HARUS men-cache refresh token dan menggunakan untuk renew access token sebelum expired. Jika refresh gagal (token di-blacklist atau revoked), client HARUS redirect ke login.

---

*DESIGN.md — K2-Net Project Reference — Sesuaikan dengan Metronic v8.2.9 CDN Assets*
