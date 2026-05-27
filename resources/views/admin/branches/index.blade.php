@extends('layouts.app')

@section('page_title', 'Branch Management')
@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard.owner') }}">Dashboard</a></li>
<li class="breadcrumb-item active">Branches</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <form method="GET" class="form-row">
            <div class="col-md-6 mb-2"><input name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="Search branch"></div>
            <div class="col-md-6 mb-2 text-right"><button class="btn btn-outline-primary">Filter</button> <a href="{{ route('admin.branches.create') }}" class="btn btn-primary">Create Branch</a></div>
        </form>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
            <thead><tr><th>Code</th><th>Name</th><th>Manager</th><th>Operational Status</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
            @forelse ($branches as $branch)
                <tr>
                    <td>{{ $branch->branch_code ?? $branch->code }}</td>
                    <td>{{ $branch->branch_name ?? $branch->name }}</td>
                    <td>{{ $branch->manager?->display_name ?? 'N/A' }}</td>
                    <td>{{ ucfirst($branch->operational_status ?? 'active') }}</td>
                    <td><span class="badge badge-{{ $branch->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($branch->status ?? 'active') }}</span></td>
                    <td><a href="{{ route('admin.branches.show', $branch) }}" class="btn btn-xs btn-outline-info">View</a> <a href="{{ route('admin.branches.edit', $branch) }}" class="btn btn-xs btn-outline-primary">Edit</a></td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center">No branches found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $branches->links() }}</div>
</div>
@endsection
