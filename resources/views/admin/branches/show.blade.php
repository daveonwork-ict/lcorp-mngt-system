@extends('layouts.app')

@section('page_title', 'Branch Profile')
@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.branches.index') }}">Branches</a></li>
<li class="breadcrumb-item active">Profile</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">{{ $branch->branch_name ?? $branch->name }}</h3>
        <a href="{{ route('admin.branches.edit', $branch) }}" class="btn btn-primary btn-sm">Edit Branch</a>
    </div>
    <div class="card-body">
        <p><strong>Branch Code:</strong> {{ $branch->branch_code ?? $branch->code }}</p>
        <p><strong>Address:</strong> {{ $branch->address }}</p>
        <p><strong>Contact:</strong> {{ $branch->contact_number ?? '-' }}</p>
        <p><strong>Email:</strong> {{ $branch->email ?? '-' }}</p>
        <p><strong>Manager:</strong> {{ $branch->manager?->display_name ?? 'N/A' }}</p>
        <p><strong>Operating Hours:</strong> {{ $branch->opening_time ?? '-' }} - {{ $branch->closing_time ?? '-' }}</p>
        <p><strong>Status:</strong> {{ ucfirst($branch->status ?? 'active') }}</p>

        <h5 class="mt-3">Assigned Users</h5>
        <ul>@foreach($branch->users as $user)<li>{{ $user->display_name }}</li>@endforeach</ul>

        <form method="POST" action="{{ route('admin.branches.status', $branch) }}" class="form-inline mt-3">
            @csrf
            <select name="status" class="form-control mr-2">@foreach(['active','inactive','maintenance','closed'] as $s)<option value="{{ $s }}" @selected(($branch->status ?? 'active') === $s)>{{ ucfirst($s) }}</option>@endforeach</select>
            <button class="btn btn-warning btn-sm">Update Status</button>
        </form>
    </div>
</div>
@endsection
