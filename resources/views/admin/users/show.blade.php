@extends('layouts.app')

@section('page_title', 'User Profile')
@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
<li class="breadcrumb-item active">Profile</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">{{ $user->display_name }}</h3>
        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary btn-sm">Edit User</a>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6"><p><strong>Employee Code:</strong> {{ $user->employee_code }}</p><p><strong>Username:</strong> {{ $user->username }}</p><p><strong>Email:</strong> {{ $user->email }}</p><p><strong>Role:</strong> {{ $user->role?->name }}</p></div>
            <div class="col-md-6"><p><strong>Primary Branch:</strong> {{ $user->primaryBranch?->name ?? 'N/A' }}</p><p><strong>Status:</strong> {{ ucfirst($user->status) }}</p><p><strong>Last Login:</strong> {{ $user->last_login_at?->format('Y-m-d H:i') ?? '-' }}</p><p><strong>Last Login IP:</strong> {{ $user->last_login_ip ?? '-' }}</p></div>
        </div>

        <h5>Assigned Branches</h5>
        <ul>@foreach($user->branches as $branch)<li>{{ $branch->name }} @if($branch->pivot->is_primary) (Primary) @endif</li>@endforeach</ul>

        <div class="mt-3">
            <a href="{{ route('admin.users.branches.edit', $user) }}" class="btn btn-outline-primary btn-sm">Manage Branch Assignment</a>
        </div>

        <hr>
        <form action="{{ route('admin.users.status', $user) }}" method="POST" class="form-inline mb-3">
            @csrf
            <label class="mr-2">Status</label>
            <select name="status" class="form-control mr-2">@foreach(['active','inactive','suspended','locked'] as $s)<option value="{{ $s }}" @selected($user->status === $s)>{{ ucfirst($s) }}</option>@endforeach</select>
            <button class="btn btn-warning btn-sm">Update Status</button>
        </form>

        <form action="{{ route('admin.users.reset-password', $user) }}" method="POST" class="form-inline">
            @csrf
            <label class="mr-2">Reset Password</label>
            <input name="password" type="password" class="form-control mr-2" placeholder="New password" required>
            <button class="btn btn-danger btn-sm">Reset</button>
        </form>
    </div>
</div>
@endsection
