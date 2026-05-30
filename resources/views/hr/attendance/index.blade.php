@extends('layouts.app')

@section('page_title', 'Attendance Monitoring')
@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
            <div class="mb-2 mb-md-0">
                <h6 class="mb-1 font-weight-bold text-primary">Quick Attendance Action</h6>
                <small class="text-muted">Record your clock-in or clock-out immediately using the button on the right.</small>
            </div>
            <a href="{{ route('hr.attendance.create') }}" class="btn btn-primary btn-lg shadow-sm">
                <i class="fas fa-user-check mr-1"></i> Record Attendance
            </a>
        </div>

        <form method="GET" class="form-row">
            <div class="col-md-3 mb-2"><select name="branch_id" class="form-control"><option value="">All Branches</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected((int)($filters['branch_id'] ?? 0) === $branch->id)>{{ $branch->branch_name ?? $branch->name }}</option>@endforeach</select></div>
            <div class="col-md-3 mb-2"><select name="user_id" class="form-control"><option value="">All Employees</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected((int)($filters['user_id'] ?? 0) === $user->id)>{{ $user->display_name }}</option>@endforeach</select></div>
            <div class="col-md-2 mb-2"><input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] ?? '' }}"></div>
            <div class="col-md-2 mb-2"><input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] ?? '' }}"></div>
            <div class="col-md-2 mb-2 text-right"><button class="btn btn-outline-primary">Filter</button></div>
        </form>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover mb-0">
            <thead><tr><th>Date</th><th>Employee</th><th>Branch</th><th>Clock In (UTC+8)</th><th>Clock Out (UTC+8)</th><th>Status</th><th>Selfie</th><th></th></tr></thead>
            <tbody>
            @forelse($attendanceLogs as $log)
                <tr>
                    <td>{{ optional($log->attendance_date)->format('Y-m-d') }}</td>
                    <td>{{ $log->user?->display_name }}</td>
                    <td>{{ $log->branch?->branch_name ?? $log->branch?->name }}</td>
                    <td>{{ $log->time_in ? $log->time_in->timezone('Asia/Manila')->format('Y-m-d H:i:s') : '-' }}</td>
                    <td>{{ $log->time_out ? $log->time_out->timezone('Asia/Manila')->format('Y-m-d H:i:s') : '-' }}</td>
                    <td>{{ ucfirst($log->attendance_status) }}</td>
                    <td>{{ $log->selfie_time_in_path ? 'Captured' : 'N/A' }}</td>
                    <td>
                        <a href="{{ route('hr.attendance.show', $log) }}" class="btn btn-xs btn-outline-secondary">View</a>
                        <a href="{{ route('hr.attendance.edit', $log) }}" class="btn btn-xs btn-outline-primary">Edit</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted">No attendance records found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $attendanceLogs->links() }}</div>
</div>
@endsection
