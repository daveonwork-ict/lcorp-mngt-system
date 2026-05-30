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
    <link rel="icon" type="image/png" href="{{ $brandingFaviconUrl ?? asset('icons/icon-192x192.svg') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <title>{{ config('app.name', 'Retail Management System (RMS)') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.7.2/css/all.min.css">
    <style>
        body { font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; }
        .main-sidebar { min-height: 100vh; }
        .content-wrapper { min-height: calc(100vh - 114px); padding-bottom: 66px; }
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
        .pagination-wrap,
        .card-footer .pagination,
        .d-flex.justify-content-between .pagination {
            margin-bottom: 0;
        }
        .pagination {
            flex-wrap: wrap;
            gap: 0.28rem;
        }
        .page-item .page-link {
            border-radius: 0.5rem;
            border: 1px solid #d7dde4;
            color: #334e68;
            font-weight: 600;
            min-width: 2.2rem;
            min-height: 2.2rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.35rem 0.65rem;
            line-height: 1;
        }
        .page-item .page-link:hover {
            background: #f0f4f8;
            color: #102a43;
            border-color: #bcccdc;
        }
        .page-item.active .page-link {
            background: #1f6feb;
            border-color: #1f6feb;
            color: #fff;
            box-shadow: 0 4px 10px rgba(31, 111, 235, 0.22);
        }
        .page-item.disabled .page-link {
            background: #f8f9fa;
            border-color: #e6e9ee;
            color: #98a6b5;
        }
        .card-footer .pagination,
        .d-flex.justify-content-between .pagination {
            justify-content: center;
        }
        .sidebar-edge-toggle {
            position: fixed;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            z-index: 1065;
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
            border-top-right-radius: 999px;
            border-bottom-right-radius: 999px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            display: none;
        }

        body.sidebar-collapse .sidebar-edge-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .main-footer {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 1035;
            margin-left: 0;
            background: #ffffff;
            border-top: 1px solid #dfe4ea;
            padding: 0.45rem 0.9rem;
        }

        body.sidebar-collapse .main-footer {
            left: 0;
        }

        @media (max-width: 767px) {
            .content-header h1 { font-size: 1.15rem; }
            .touch-btn { width: 100%; }
            .table-responsive { font-size: 0.88rem; }
            .main-sidebar { width: 250px; }
            .content-wrapper { min-height: calc(100vh - 102px); padding-bottom: 74px; }
            .small-box .inner h4 { font-size: 1.15rem; }
            .pagination {
                justify-content: center;
            }
            .page-item .page-link {
                min-width: 2.35rem;
                min-height: 2.35rem;
                font-size: 0.95rem;
            }
            .sidebar-edge-toggle {
                display: none !important;
            }

            .main-footer {
                left: 0;
                padding: 0.5rem 0.75rem;
            }
        }
    </style>
    @stack('styles')
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

<button type="button" class="btn btn-primary sidebar-edge-toggle" data-widget="pushmenu" aria-label="Expand sidebar">
    <i class="fas fa-chevron-right"></i>
</button>

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

(function () {
    const sidebarStateKey = 'rms.sidebar.collapsed';

    const persistSidebarState = function () {
        const collapsed = document.body.classList.contains('sidebar-collapse');
        localStorage.setItem(sidebarStateKey, collapsed ? '1' : '0');
    };

    const applyStoredSidebarState = function () {
        if (window.innerWidth < 992) {
            return;
        }

        const stored = localStorage.getItem(sidebarStateKey);
        if (stored === '1') {
            document.body.classList.add('sidebar-collapse');
        } else if (stored === '0') {
            document.body.classList.remove('sidebar-collapse');
        }
    };

    applyStoredSidebarState();

    document.addEventListener('click', function (event) {
        if (!event.target.closest('[data-widget="pushmenu"]')) {
            return;
        }

        setTimeout(persistSidebarState, 120);
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth < 992) {
            return;
        }

        applyStoredSidebarState();
    });

    const storageKey = 'rms.sidebar.scrollTop';

    const resolveSidebarScroller = function () {
        return document.querySelector('.main-sidebar .sidebar')
            || document.querySelector('.main-sidebar .os-content')
            || null;
    };

    const saveSidebarScroll = function () {
        const scroller = resolveSidebarScroller();
        if (!scroller) return;

        sessionStorage.setItem(storageKey, String(scroller.scrollTop || 0));
    };

    const restoreSidebarScroll = function () {
        const scroller = resolveSidebarScroller();
        if (!scroller) return;

        const saved = Number(sessionStorage.getItem(storageKey) || 0);
        if (Number.isFinite(saved) && saved > 0) {
            scroller.scrollTop = saved;
            return;
        }

        const activeLink = document.querySelector('.main-sidebar .nav-link.active');
        if (activeLink) {
            activeLink.scrollIntoView({ block: 'nearest' });
        }
    };

    document.addEventListener('click', function (event) {
        if (!event.target.closest('.main-sidebar a.nav-link')) {
            return;
        }

        saveSidebarScroll();
    });

    window.addEventListener('beforeunload', saveSidebarScroll);

    window.addEventListener('load', function () {
        restoreSidebarScroll();
        setTimeout(restoreSidebarScroll, 120);
        setTimeout(restoreSidebarScroll, 350);
    });
})();
</script>
@stack('scripts')
</body>
</html>
