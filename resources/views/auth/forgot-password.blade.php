@extends('layouts.auth')

@section('content')
<div class="login-box">
    <div class="card card-outline card-info">
        <div class="card-body login-card-body">
            <h4 class="text-center mb-3">Forgot Password</h4>
            <p class="text-muted">Enter your email to request a reset link.</p>
            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <div class="input-group mb-3">
                    <input type="email" name="email" class="form-control" placeholder="Email" required>
                    <div class="input-group-append"><div class="input-group-text"><span class="fas fa-envelope"></span></div></div>
                </div>
                <button class="btn btn-info btn-block">Send Reset Link</button>
            </form>
            <p class="mt-3 mb-0 text-center"><a href="{{ route('login') }}">Back to login</a></p>
        </div>
    </div>
</div>
@endsection
