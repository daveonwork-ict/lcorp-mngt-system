@extends('layouts.app')

@section('page_title', 'Employee Profiles')
@section('content')
<div class="card">
    <div class="card-header">
        <form method="GET" class="form-row">
            <div class="col-md-4 mb-2"><input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="Search employee"></div>
            <div class="col-md-3 mb-2"><select name="branch_id" class="form-control"><option value="">All Branches</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected((int)($filters['branch_id'] ?? 0) === $branch->id)>{{ $branch->branch_name ?? $branch->name }}</option>@endforeach</select></div>
            <div class="col-md-3 mb-2"><select name="employment_status" class="form-control"><option value="">All Status</option>@foreach(['active','inactive','resigned','terminated','on_leave'] as $status)<option value="{{ $status }}" @selected(($filters['employment_status'] ?? '') === $status)>{{ ucfirst(str_replace('_',' ',$status)) }}</option>@endforeach</select></div>
            <div class="col-md-2 mb-2 text-right"><button class="btn btn-outline-primary">Filter</button> <a href="{{ route('hr.employees.create') }}" class="btn btn-primary">Create</a></div>
        </form>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover mb-0">
            <thead><tr><th>Employee</th><th>Branch</th><th>Position</th><th>Type</th><th>Status</th><th>Salary</th><th></th></tr></thead>
            <tbody>
            @forelse($profiles as $profile)
                <tr>
                    <td>{{ $profile->user?->display_name }}<br><small class="text-muted">{{ $profile->user?->employee_code }}</small></td>
                    <td>{{ $profile->branch?->branch_name ?? $profile->branch?->name ?? '-' }}</td>
                    <td>{{ $profile->position?->position_name ?? '-' }}</td>
                    <td>{{ ucfirst(str_replace('_',' ', $profile->employment_type)) }}</td>
                    <td>{{ ucfirst(str_replace('_',' ', $profile->employment_status)) }}</td>
                    <td>{{ ucfirst($profile->salary_type) }} / {{ number_format((float) $profile->salary_rate, 2) }}</td>
                    <td><a href="{{ route('hr.employees.edit', $profile) }}" class="btn btn-xs btn-outline-primary">Edit</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted">No employee profiles found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $profiles->links() }}</div>
</div>
@endsection
