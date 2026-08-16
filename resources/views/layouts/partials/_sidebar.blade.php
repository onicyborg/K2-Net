{{-- Sidebar --}}
<div id="kt_app_sidebar" class="app-sidebar flex-column"
     data-kt-drawer="true"
     data-kt-drawer-name="app-sidebar"
     data-kt-drawer-activate="{default: true, lg: false}"
     data-kt-drawer-overlay="true"
     data-kt-drawer-width="225px"
     data-kt-drawer-direction="start"
     data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">

    {{-- Logo --}}
    <div class="app-sidebar-logo px-6" id="kt_app_sidebar_logo">
        <a href="{{ route('admin.dashboard') }}">
            <img alt="Logo" src="{{ url('images/logo-text-dark-medium.png') }}"
                 class="h-30px app-sidebar-logo-default" />
            <img alt="Logo" src="{{ url('images/logo-icon-dark-80.png') }}"
                 class="h-30px app-sidebar-logo-minimize" />
        </a>
    </div>

    {{-- Menu wrapper + scroll wrapper --}}
    <div class="app-sidebar-menu overflow-hidden flex-column-fluid" id="kt_app_sidebar_menu_wrapper">
        <div id="kt_app_sidebar_menu_scroll" class="scroll-y my-5 mx-3"
             data-kt-scroll="true"
             data-kt-scroll-activate="true"
             data-kt-scroll-height="auto"
             data-kt-scroll-dependencies="#kt_app_sidebar_logo, #kt_app_sidebar_footer"
             data-kt-scroll-save-state="true">

            <div class="menu menu-column menu-rounded menu-sub-indention fw-semibold fs-6"
                 id="kt_app_sidebar_menu"
                 data-kt-menu="true"
                 data-kt-menu-expand="false">

                {{-- Dashboard --}}
                <div class="menu-item">
                    <a class="menu-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                       href="{{ route('admin.dashboard') }}">
                        <span class="menu-icon">
                            <i class="ki-duotone ki-home fs-2"></i>
                        </span>
                        <span class="menu-title">Dashboard</span>
                    </a>
                </div>

                {{-- Divider: Master Data --}}
                <div class="menu-item pt-5">
                    <div class="menu-content">
                        <span class="menu-heading fw-bold text-uppercase fs-7">Master Data</span>
                    </div>
                </div>

                {{-- Pelanggan --}}
                <div class="menu-item">
                    <a class="menu-link {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}"
                       href="{{ route('admin.customers.index') }}">
                        <span class="menu-icon">
                            <i class="ki-duotone ki-profile-circle fs-2">
                                <span class="path1"></span><span class="path2"></span>
                            </i>
                        </span>
                        <span class="menu-title">Pelanggan</span>
                    </a>
                </div>

                {{-- Paket Internet --}}
                <div class="menu-item">
                    <a class="menu-link {{ request()->routeIs('admin.packages.*') ? 'active' : '' }}"
                       href="{{ route('admin.packages.index') }}">
                        <span class="menu-icon">
                            <i class="ki-duotone ki-package fs-2">
                                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                            </i>
                        </span>
                        <span class="menu-title">Paket Internet</span>
                    </a>
                </div>

                {{-- Divider: Transaksi --}}
                <div class="menu-item pt-5">
                    <div class="menu-content">
                        <span class="menu-heading fw-bold text-uppercase fs-7">Transaksi</span>
                    </div>
                </div>

                {{-- Tagihan --}}
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

                {{-- Verifikasi Pembayaran --}}
                <div class="menu-item">
                    <a class="menu-link {{ request()->routeIs('admin.verifications.*') ? 'active' : '' }}"
                       href="{{ route('admin.verifications.index') }}">
                        <span class="menu-icon">
                            <i class="ki-duotone ki-check-circle fs-2">
                                <span class="path1"></span><span class="path2"></span>
                            </i>
                        </span>
                        <span class="menu-title">Verifikasi Pembayaran</span>
                    </a>
                </div>

                {{-- Divider: Sistem --}}
                <div class="menu-item pt-5">
                    <div class="menu-content">
                        <span class="menu-heading fw-bold text-uppercase fs-7">Sistem</span>
                    </div>
                </div>

                {{-- Pelaporan --}}
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

                {{-- Log Notifikasi --}}
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

                {{-- Konfigurasi --}}
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

            </div>
        </div>
    </div>

    {{-- Sidebar Footer --}}
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
