<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
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

    @stack('styles')
    <style>
        .theme-logo { display: inline-block; }
        .theme-logo-dark  { display: none; }
        [data-bs-theme="dark"] .theme-logo-light { display: none; }
        [data-bs-theme="dark"] .theme-logo-dark  { display: inline-block; }
    </style>
</head>
<body id="kt_body" class="app-blank bgi-size-cover bgi-position-center bgi-no-repeat">

    <script>
    var defaultThemeMode = "light";
    var themeMode = localStorage.getItem("data-bs-theme") || defaultThemeMode;
    if (themeMode === "system") {
        themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
    }
    document.documentElement.setAttribute("data-bs-theme", themeMode);
    </script>
    <style>
    body {
        background-image: url('https://magang.skripsian.site/assets/media/auth/bg1.jpg');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }
    [data-bs-theme="dark"] body,
    [data-bs-theme="dark"] #kt_body {
        background-image: url('https://magang.skripsian.site/assets/media/auth/bg1-dark.jpg');
    }
    </style>

    <div class="d-flex flex-column flex-root">
        <div class="d-flex flex-column flex-center flex-column-fluid">
            <div class="d-flex flex-column flex-center text-center p-10">
                @yield('content')
            </div>
        </div>
    </div>

    <script>var hostUrl = "https://magang.skripsian.site/assets/";</script>
    <script src="https://magang.skripsian.site/assets/plugins/global/plugins.bundle.js"></script>
    <script src="https://magang.skripsian.site/assets/js/scripts.bundle.js"></script>

    @stack('scripts')
</body>
</html>
