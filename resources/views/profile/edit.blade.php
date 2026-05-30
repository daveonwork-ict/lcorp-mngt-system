@extends('layouts.app')

@section('page_title', 'My Profile')
@section('content')
<div class="row">
    <div class="col-lg-7">
        <div class="card shadow-sm mb-3">
            <div class="card-header"><strong>Profile Information</strong></div>
            <div class="card-body">
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>First Name</label>
                            <input type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}" class="form-control @error('first_name') is-invalid @enderror">
                            @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group col-md-4">
                            <label>Middle Name</label>
                            <input type="text" name="middle_name" value="{{ old('middle_name', $user->middle_name) }}" class="form-control @error('middle_name') is-invalid @enderror">
                            @error('middle_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group col-md-3">
                            <label>Last Name</label>
                            <input type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}" class="form-control @error('last_name') is-invalid @enderror">
                            @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group col-md-1">
                            <label>Suffix</label>
                            <input type="text" name="suffix" value="{{ old('suffix', $user->suffix) }}" class="form-control @error('suffix') is-invalid @enderror">
                            @error('suffix')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Username</label>
                            <input type="text" name="username" value="{{ old('username', $user->username) }}" class="form-control @error('username') is-invalid @enderror" required>
                            @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group col-md-6">
                            <label>Email Address</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Mobile Number</label>
                            <input type="text" name="mobile_number" value="{{ old('mobile_number', $user->mobile_number) }}" class="form-control @error('mobile_number') is-invalid @enderror">
                            @error('mobile_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group col-md-6">
                            <label>Employee Code</label>
                            <input type="text" class="form-control" value="{{ $user->employee_code }}" readonly>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Profile</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card shadow-sm mb-3">
            <div class="card-header"><strong>Change Password</strong></div>
            <div class="card-body">
                <form method="POST" action="{{ route('profile.password.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                        @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-outline-primary">Update Password</button>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header"><strong>Account Summary</strong></div>
            <div class="card-body">
                <p class="mb-1"><strong>Role:</strong> {{ $user->role?->name ?? 'N/A' }}</p>
                <p class="mb-1"><strong>Status:</strong> {{ strtoupper((string) ($user->status ?? 'active')) }}</p>
                <p class="mb-0"><strong>Primary Branch:</strong> {{ $user->primaryBranch?->name ?? 'N/A' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
