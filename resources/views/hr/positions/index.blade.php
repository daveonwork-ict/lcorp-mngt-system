@extends('layouts.app')

@section('page_title', 'HR Positions')
@section('content')
<div class="card">
    <div class="card-header">
        <form method="GET" class="form-row">
            <div class="col-md-4 mb-2"><input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="Search position"></div>
            <div class="col-md-3 mb-2">
                <select name="status" class="form-control">
                    <option value="">All status</option>
                    @foreach (['active', 'inactive'] as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-5 mb-2 text-right">
                <button class="btn btn-outline-primary">Filter</button>
                <a href="{{ route('hr.positions.create') }}" class="btn btn-primary">Create Position</a>
            </div>
        </form>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover mb-0">
            <thead><tr><th>Code</th><th>Name</th><th>Department</th><th>Salary Type</th><th>Rate</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse ($positions as $position)
                <tr>
                    <td>{{ $position->position_code }}</td>
                    <td>{{ $position->position_name }}</td>
                    <td>{{ $position->department ?: '-' }}</td>
                    <td>{{ ucfirst($position->salary_type) }}</td>
                    <td>{{ number_format((float) $position->default_salary_rate, 2) }}</td>
                    <td>{{ ucfirst($position->status) }}</td>
                    <td><a href="{{ route('hr.positions.edit', $position) }}" class="btn btn-xs btn-outline-primary">Edit</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted">No positions found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $positions->links() }}</div>
</div>
@endsection
