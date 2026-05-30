@extends('layouts.app')

@section('page_title', 'Employee Schedules')
@section('content')
<div class="card">
    <div class="card-header">
        <form method="GET" class="form-row">
            <div class="col-md-3 mb-2"><select name="branch_id" class="form-control"><option value="">All Branches</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected((int)($filters['branch_id'] ?? 0) === $branch->id)>{{ $branch->branch_name ?? $branch->name }}</option>@endforeach</select></div>
            <div class="col-md-3 mb-2"><select name="user_id" class="form-control"><option value="">All Employees</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected((int)($filters['user_id'] ?? 0) === $user->id)>{{ $user->display_name }}</option>@endforeach</select></div>
            <div class="col-md-2 mb-2"><input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] ?? '' }}"></div>
            <div class="col-md-2 mb-2"><input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] ?? '' }}"></div>
            <div class="col-md-2 mb-2 text-right"><button class="btn btn-outline-primary">Filter</button> <a href="{{ route('hr.schedules.create') }}" class="btn btn-primary">Create</a></div>
        </form>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover mb-0">
            <thead><tr><th>Date</th><th>Employee</th><th>Branch</th><th>Type</th><th>Time In</th><th>Time Out</th><th>Rest Day</th><th></th></tr></thead>
            <tbody>
            @forelse($schedules as $schedule)
                <tr>
                    <td>{{ optional($schedule->schedule_date)->format('Y-m-d') }}</td>
                    <td>{{ $schedule->user?->display_name }}</td>
                    <td>{{ $schedule->branch?->branch_name ?? $schedule->branch?->name }}</td>
                    <td>{{ ucfirst($schedule->schedule_type) }}</td>
                    <td>{{ $schedule->time_in ?: '-' }}</td>
                    <td>{{ $schedule->time_out ?: '-' }}</td>
                    <td>{{ $schedule->is_rest_day ? 'Yes' : 'No' }}</td>
                    <td><a href="{{ route('hr.schedules.edit', $schedule) }}" class="btn btn-xs btn-outline-primary">Edit</a></td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted">No schedules found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $schedules->links() }}</div>
</div>
@endsection
