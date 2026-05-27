<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'RC Store RMS') }} - Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.7.2/css/all.min.css">
    <style>
        body { background: linear-gradient(145deg, #0b1727, #1f2f46); min-height: 100vh; }
        .login-box { width: min(92vw, 420px); }
        .brand-ribbon { background: linear-gradient(110deg, #f59e0b, #ea580c); color: #fff; border-radius: 999px; padding: 4px 12px; font-size: .78rem; }
    </style>
</head>
<body class="hold-transition login-page">
    @yield('content')
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
