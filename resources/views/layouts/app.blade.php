<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#1f2937">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <title>{{ config('app.name', 'RC Store RMS') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.7.2/css/all.min.css">
    <style>
        body { font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; }
        .main-sidebar { min-height: 100vh; }
        .content-wrapper { min-height: calc(100vh - 114px); }
        .metric-card { min-height: 118px; }
        .touch-btn { min-height: 52px; font-size: 1rem; }
        .module-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px; }
        .chart-wrap { min-height: 320px; }
        .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .card-body .form-row > [class*="col-"] { min-width: 0; }
        .pwa-install-btn {
            position: fixed;
            right: 14px;
            bottom: 14px;
            z-index: 1075;
            border-radius: 999px;
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.2);
        }
        .chart-card canvas { width: 100% !important; }
        @media (max-width: 767px) {
            .content-header h1 { font-size: 1.15rem; }
            .touch-btn { width: 100%; }
            .table-responsive { font-size: 0.88rem; }
            .main-sidebar { width: 250px; }
            .content-wrapper { min-height: calc(100vh - 102px); }
            .small-box .inner h4 { font-size: 1.15rem; }
        }
    </style>
    @stack('head')
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed">
<div class="wrapper">
    @include('layouts.partials.topbar')
    @include('layouts.partials.sidebar')

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">@yield('page_title', 'Dashboard')</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            @yield('breadcrumbs')
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                @if (session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif
                @yield('content')
            </div>
        </section>
    </div>

    @include('layouts.partials.footer')
</div>

<button id="pwaInstallBtn" class="btn btn-primary pwa-install-btn d-none" type="button" aria-label="Install app">
    <i class="fas fa-download mr-1"></i>Install App
</button>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
        navigator.serviceWorker.register('/sw.js').catch(function () {});
    });
}

(function () {
    const installBtn = document.getElementById('pwaInstallBtn');
    if (!installBtn) return;

    let deferredPrompt = null;
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;

    if (!isStandalone && !('onbeforeinstallprompt' in window)) {
        installBtn.classList.remove('d-none');
        installBtn.classList.replace('btn-primary', 'btn-outline-primary');
        installBtn.innerHTML = '<i class="fas fa-mobile-alt mr-1"></i>Add to Home Screen';
        installBtn.addEventListener('click', function () {
            alert('To install RC Store RMS: open browser menu and choose "Install app" or "Add to Home Screen".');
        });
    }

    window.addEventListener('beforeinstallprompt', function (event) {
        event.preventDefault();
        deferredPrompt = event;
        installBtn.classList.remove('d-none');
    });

    installBtn.addEventListener('click', async function () {
        if (!deferredPrompt) return;
        deferredPrompt.prompt();
        await deferredPrompt.userChoice;
        deferredPrompt = null;
        installBtn.classList.add('d-none');
    });

    window.addEventListener('appinstalled', function () {
        installBtn.classList.add('d-none');
        fetch('{{ route('pwa.installed') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify({ installed: true })
        }).catch(function () {});
    });
})();
</script>
@stack('scripts')
</body>
</html>
