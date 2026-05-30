@extends('layouts.app')

@section('page_title', 'Leave Requests')
@section('content')
<div class="card">
    <div class="card-header">
        <form method="GET" class="form-row">
            <div class="col-md-3 mb-2"><select name="branch_id" class="form-control"><option value="">All Branches</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected((int)($filters['branch_id'] ?? 0) === $branch->id)>{{ $branch->branch_name ?? $branch->name }}</option>@endforeach</select></div>
            <div class="col-md-3 mb-2"><select name="status" class="form-control"><option value="">All Statuses</option>@foreach(['pending_manager','pending_hr','approved','rejected','cancelled'] as $status)<option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>@endforeach</select></div>
            <div class="col-md-6 mb-2 text-right"><button class="btn btn-outline-primary">Filter</button> <a href="{{ route('hr.leaves.create') }}" class="btn btn-primary">Create Leave</a></div>
        </form>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover mb-0">
            <thead><tr><th>Employee</th><th>Branch</th><th>Type</th><th>Start</th><th>End</th><th>Days</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse($leaveRequests as $leave)
                <tr>
                    <td>{{ $leave->user?->display_name }}</td>
                    <td>{{ $leave->branch?->branch_name ?? $leave->branch?->name }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $leave->leave_type)) }}</td>
                    <td>{{ optional($leave->start_date)->format('Y-m-d') }}</td>
                    <td>{{ optional($leave->end_date)->format('Y-m-d') }}</td>
                    <td>{{ number_format((float) $leave->total_days, 2) }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $leave->status)) }}</td>
                    <td><a href="{{ route('hr.leaves.edit', $leave) }}" class="btn btn-xs btn-outline-primary">Edit</a></td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted">No leave requests found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $leaveRequests->links() }}</div>
</div>
@endsection
