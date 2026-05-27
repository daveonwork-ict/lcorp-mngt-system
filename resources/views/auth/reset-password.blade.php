@extends('layouts.auth')

@section('content')
<div class="login-box">
    <div class="card card-outline card-success">
        <div class="card-body login-card-body">
            <h4 class="text-center mb-3">Reset Password</h4>
            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div class="input-group mb-3"><input type="email" name="email" class="form-control" value="{{ old('email', $email) }}" required><div class="input-group-append"><div class="input-group-text"><span class="fas fa-envelope"></span></div></div></div>
                <div class="input-group mb-3"><input type="password" name="password" class="form-control" placeholder="New password" required><div class="input-group-append"><div class="input-group-text"><span class="fas fa-lock"></span></div></div></div>
                <div class="input-group mb-3"><input type="password" name="password_confirmation" class="form-control" placeholder="Confirm password" required><div class="input-group-append"><div class="input-group-text"><span class="fas fa-lock"></span></div></div></div>
                <button class="btn btn-success btn-block">Reset Password</button>
            </form>
        </div>
    </div>
</div>
@endsection
