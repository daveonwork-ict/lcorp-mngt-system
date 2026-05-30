@extends('layouts.auth')

@section('content')
<div class="login-box">
    <div class="card card-outline card-warning shadow-lg auth-card">
        <div class="card-body login-card-body">
            <div class="text-center mb-4">
                <div class="mb-2">
                    <img src="{{ $brandingLoginLogoUrl ?? asset('images/dits_logo.png') }}" alt="Daveonwork IT Solutions" style="max-width: 260px; width: 100%; height: auto;" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-block';">
                    <span style="display:none; font-weight:700; color:#1f2f46; font-size:0.92rem;">Daveonwork IT Solutions</span>
                </div>
                <span class="brand-ribbon">Retail Operations Suite</span>
                <h3 class="mt-3 mb-1">Retail Management System (RMS)</h3>
                <p class="text-muted mb-0">Secure access for branch operations, finance, HR, and analytics.</p>
            </div>

            <form action="{{ route('login.store') }}" method="post">
                @csrf
                <div class="input-group mb-3">
                    <input type="text" name="login" class="form-control @error('login') is-invalid @enderror" placeholder="Username or email" value="{{ old('login') }}" required autofocus>
                    <div class="input-group-append"><div class="input-group-text"><span class="fas fa-user"></span></div></div>
                </div>
                @error('login')
                    <div class="text-danger small mb-2">{{ $message }}</div>
                @enderror

                <div class="input-group mb-3">
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Password" required>
                    <div class="input-group-append"><div class="input-group-text"><span class="fas fa-lock"></span></div></div>
                </div>
                @error('password')
                    <div class="text-danger small mb-2">{{ $message }}</div>
                @enderror

                <div class="row mb-2">
                    <div class="col-8">
                        <div class="icheck-primary">
                            <input type="checkbox" id="remember" name="remember" value="1" @checked(old('remember'))>
                            <label for="remember">Remember me</label>
                        </div>
                    </div>
                    <div class="col-4 text-right">
                        <a href="{{ route('password.request') }}" class="small">Forgot?</a>
                    </div>
                </div>

                <button type="submit" class="btn btn-warning btn-block font-weight-bold">Sign In</button>
                <a href="{{ route('login.demo') }}" class="btn btn-outline-secondary btn-block mt-2">Open Demo Login Page</a>
                <a href="{{ asset('manuals/rms-user-manual.html') }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-info btn-block mt-2">Open RMS User Manual</a>
                <p class="text-muted small mt-3 mb-0 text-center">Developed by Daveonwork IT Solutions</p>
            </form>
        </div>
    </div>
</div>
@endsection
