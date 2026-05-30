<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Retail Management System (RMS)') }} - Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.7.2/css/all.min.css">
    <style>
        :root {
            --rms-navy: #0e2540;
            --rms-ink: #1f2f46;
            --rms-sunset: #f59e0b;
            --rms-slate: #e5ecf4;
        }
        body {
            min-height: 100vh;
            margin: 0;
            background:
                radial-gradient(1200px 480px at 15% -10%, rgba(245, 158, 11, 0.28), transparent 50%),
                radial-gradient(900px 380px at 100% 0%, rgba(54, 92, 151, 0.35), transparent 56%),
                linear-gradient(145deg, #081426, #11253d 48%, #152f4f);
            color: #e8edf3;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 20px;
        }
        .login-box {
            width: min(96vw, 430px);
            margin: 0 auto;
        }
        .brand-ribbon {
            background: linear-gradient(110deg, #f59e0b, #ea580c);
            color: #fff;
            border-radius: 999px;
            padding: 4px 12px;
            font-size: 0.74rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            display: inline-block;
        }
        .auth-card {
            border: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(6px);
            border-radius: 16px;
            box-shadow: 0 25px 40px rgba(2, 10, 22, 0.34);
            overflow: hidden;
        }
        .auth-card .login-card-body {
            border-radius: 16px;
            padding: 1.45rem;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafd 100%);
        }
        .auth-footer-note {
            margin-top: 14px;
            text-align: center;
            font-size: 0.78rem;
            opacity: 0.95;
        }
        .auth-footer-note strong {
            color: #ffd48a;
            font-weight: 700;
        }
        @media (max-width: 576px) {
            body {
                padding: 14px;
            }
            .auth-card .login-card-body {
                padding: 1.1rem;
            }
        }
    </style>
</head>
<body class="hold-transition login-page">
    @yield('content')
    <div class="auth-footer-note">
        {{ config('app.name') }} | Developed by <strong>Daveonwork IT Solutions</strong>
    </div>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
