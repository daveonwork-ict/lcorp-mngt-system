@extends('layouts.auth')

@section('content')
<div class="login-box">
    <div class="card card-outline card-warning shadow-lg">
        <div class="card-body login-card-body">
            <div class="text-center mb-3">
                <span class="brand-ribbon">Retail Management Platform</span>
                <h3 class="mt-3 mb-1">RC Store RMS</h3>
                <p class="text-muted">Multi-branch operations and analytics</p>
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

                <button type="submit" class="btn btn-warning btn-block">Login</button>
                <p class="text-muted small mt-3 mb-0">Security-ready: CSRF protected, input validated, and rate-limited endpoint.</p>
            </form>
        </div>
    </div>
</div>
@endsection
