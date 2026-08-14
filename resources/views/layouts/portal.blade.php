<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', 'K2-Net Portal')</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <link href="https://magang.skripsian.site/assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="https://magang.skripsian.site/assets/css/style.bundle.css" rel="stylesheet" type="text/css" />

    @stack('styles')
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
    [data-bs-theme="dark"] #kt_body {
        background-image: url('https://magang.skripsian.site/assets/media/auth/bg1-dark.jpg');
    }
    .portal-header {
        border-bottom: 1px solid rgba(255,255,255,0.1);
        backdrop-filter: blur(10px);
    }
    @stack('extra_styles')
    </style>

    <div class="d-flex flex-column flex-root">
        <div class="d-flex flex-column flex-center flex-column-fluid">
            <div class="d-flex flex-column flex-center text-center p-10">
                @yield('content')
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>var hostUrl = "https://magang.skripsian.site/assets/";</script>
    <script src="https://magang.skripsian.site/assets/plugins/global/plugins.bundle.js"></script>
    <script src="https://magang.skripsian.site/assets/js/scripts.bundle.js"></script>

    @stack('scripts')
</body>
</html>
