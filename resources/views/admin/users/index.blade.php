@extends('layouts.app')

@section('page_title', 'User Management')
@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard.owner') }}">Dashboard</a></li>
<li class="breadcrumb-item active">Users</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <form method="GET" class="form-row">
            <div class="col-md-4 mb-2"><input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="Search user"></div>
            <div class="col-md-3 mb-2">
                <select name="status" class="form-control">
                    <option value="">All status</option>
                    @foreach (['active','inactive','suspended','locked'] as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-5 mb-2 text-right">
                <button class="btn btn-outline-primary">Filter</button>
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary">Create User</a>
            </div>
        </form>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
            <thead><tr><th>Employee</th><th>Name</th><th>Role</th><th>Primary Branch</th><th>Status</th><th>Last Login</th><th>Action</th></tr></thead>
            <tbody>
            @forelse ($users as $user)
                <tr>
                    <td>{{ $user->employee_code }}</td>
                    <td>{{ $user->display_name }}</td>
                    <td>{{ $user->role?->name ?? 'N/A' }}</td>
                    <td>{{ $user->primaryBranch?->name ?? 'N/A' }}</td>
                    <td><span class="badge badge-{{ $user->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($user->status) }}</span></td>
                    <td>{{ $user->last_login_at?->format('Y-m-d H:i') ?? '-' }}</td>
                    <td>
                        <a href="{{ route('admin.users.show', $user) }}" class="btn btn-xs btn-outline-info">View</a>
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-xs btn-outline-primary">Edit</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center">No users found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $users->links() }}</div>
</div>
@endsection
