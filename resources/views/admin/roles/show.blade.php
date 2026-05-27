@extends('layouts.app')

@section('page_title', 'Role Profile')
@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.roles.index') }}">Roles</a></li>
<li class="breadcrumb-item active">Profile</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">{{ $role->name }}</h3>
        <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-primary btn-sm">Edit Role</a>
    </div>
    <div class="card-body">
        <p><strong>Code:</strong> {{ $role->code }}</p>
        <p><strong>Status:</strong> {{ ucfirst($role->status) }}</p>
        <p><strong>Description:</strong> {{ $role->description ?? '-' }}</p>

        <h5 class="mt-4">Permissions</h5>
        <div class="row">@foreach($role->permissions as $permission)<div class="col-md-4 mb-1"><span class="badge badge-light border">{{ $permission->name }}</span></div>@endforeach</div>

        <h5 class="mt-4">Assigned Users</h5>
        <ul>@foreach($role->users as $user)<li>{{ $user->display_name }}</li>@endforeach</ul>

        <form method="POST" action="{{ route('admin.roles.status', $role) }}" class="form-inline mt-3">
            @csrf
            <select name="status" class="form-control mr-2">@foreach(['active','inactive'] as $s)<option value="{{ $s }}" @selected($role->status === $s)>{{ ucfirst($s) }}</option>@endforeach</select>
            <button class="btn btn-warning btn-sm">Update Status</button>
        </form>
    </div>
</div>
@endsection
