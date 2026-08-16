<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', 'K2-Net')</title>

    <link rel="icon" type="image/x-icon" href="{{ url('favicon.ico') }}" />
    <link rel="shortcut icon" href="{{ url('favicon.ico') }}" />

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <link href="https://magang.skripsian.site/assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="https://magang.skripsian.site/assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
    <link href="https://magang.skripsian.site/assets/plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css" />

    @stack('styles')
    <style>
        .theme-logo { display: inline-block; }
        .theme-logo-dark  { display: none; }
        [data-bs-theme="dark"] .theme-logo-light { display: none; }
        [data-bs-theme="dark"] .theme-logo-dark  { display: inline-block; }
    </style>
</head>
<body id="kt_app_body"
      data-kt-app-layout="dark-sidebar"
      data-kt-app-header-fixed="true"
      data-kt-app-sidebar-enabled="true"
      data-kt-app-sidebar-fixed="true"
      data-kt-app-sidebar-hoverable="true"
      data-kt-app-sidebar-push-header="true"
      data-kt-app-sidebar-push-toolbar="true"
      data-kt-app-sidebar-push-footer="true"
      data-kt-app-toolbar-enabled="true"
      class="app-default">

    <script>
    var defaultThemeMode = "light";
    var themeMode;
    if (document.documentElement.hasAttribute("data-bs-theme-mode")) {
        themeMode = document.documentElement.getAttribute("data-bs-theme-mode");
    } else {
        themeMode = localStorage.getItem("data-bs-theme") || defaultThemeMode;
    }
    if (themeMode === "system") {
        themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
    }
    document.documentElement.setAttribute("data-bs-theme", themeMode);
    </script>

    <div class="d-flex flex-column flex-root app-root" id="kt_app_root">
        <div class="app-page flex-column flex-column-fluid" id="kt_app_page">

            @auth
                @include('layouts.partials._header')
            @endauth

            <div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">

                @auth
                    @include('layouts.partials._sidebar')
                @endauth

                <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
                    <div class="d-flex flex-column flex-column-fluid overflow-auto">

                        @hasSection('toolbar')
                        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                            <div class="app-container container-fluid d-flex flex-wrap flex-sm-nowrap align-items-center gap-3">
                                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-0 me-sm-3 min-w-0">
                                    @yield('toolbar')
                                </div>
                                @hasSection('toolbar_actions')
                                <div class="d-flex align-items-center ms-sm-auto py-2">
                                    @yield('toolbar_actions')
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif

                        <div id="kt_app_content" class="app-content flex-column-fluid">
                            <div class="app-container container-fluid py-5">
                                @yield('content')
                            </div>
                        </div>

                        @hasSection('footer')
                            @yield('footer')
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>var hostUrl = "https://magang.skripsian.site/assets/";</script>
    <script src="https://magang.skripsian.site/assets/plugins/global/plugins.bundle.js"></script>
    <script src="https://magang.skripsian.site/assets/js/scripts.bundle.js"></script>
    <script src="https://magang.skripsian.site/assets/plugins/custom/datatables/datatables.bundle.js"></script>

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

        applyTheme(getThemeMode());

        document.addEventListener("click", function (e) {
            var trigger = e.target.closest("[data-kt-element=\"mode\"]");
            if (!trigger) return;
            var mode = trigger.getAttribute("data-kt-value");
            if (!mode) return;
            applyTheme(mode);
        });
    })();
    </script>

    @stack('scripts')
</body>
</html>
