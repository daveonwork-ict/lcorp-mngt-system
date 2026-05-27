@extends('layouts.app')

@section('page_title', 'Role Management')
@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard.owner') }}">Dashboard</a></li>
<li class="breadcrumb-item active">Roles</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header text-right"><a href="{{ route('admin.roles.create') }}" class="btn btn-primary">Create Role</a></div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
            <thead><tr><th>Code</th><th>Name</th><th>Status</th><th>Users</th><th>Action</th></tr></thead>
            <tbody>
            @forelse ($roles as $role)
                <tr>
                    <td>{{ $role->code }}</td>
                    <td>{{ $role->name }}</td>
                    <td><span class="badge badge-{{ $role->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($role->status) }}</span></td>
                    <td>{{ $role->users_count }}</td>
                    <td><a href="{{ route('admin.roles.show', $role) }}" class="btn btn-xs btn-outline-info">View</a> <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-xs btn-outline-primary">Edit</a></td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center">No roles found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $roles->links() }}</div>
</div>
@endsection
