@extends('layouts.app')

@section('page_title', 'Overtime Management')
@section('content')
<div class="card">
    <div class="card-header">
        <form method="GET" class="form-row">
            <div class="col-md-3 mb-2"><select name="branch_id" class="form-control"><option value="">All Branches</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected((int)($filters['branch_id'] ?? 0) === $branch->id)>{{ $branch->branch_name ?? $branch->name }}</option>@endforeach</select></div>
            <div class="col-md-3 mb-2"><select name="status" class="form-control"><option value="">All Statuses</option>@foreach(['pending_manager','pending_hr','approved','rejected','cancelled'] as $status)<option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>@endforeach</select></div>
            <div class="col-md-6 mb-2 text-right"><button class="btn btn-outline-primary">Filter</button> <a href="{{ route('hr.overtime.create') }}" class="btn btn-primary">Create Overtime</a></div>
        </form>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover mb-0">
            <thead><tr><th>Date</th><th>Employee</th><th>Branch</th><th>Hours</th><th>Status</th><th>Reason</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($overtimeRequests as $request)
                <tr>
                    <td>{{ optional($request->overtime_date)->format('Y-m-d') }}</td>
                    <td>{{ $request->user?->display_name }}</td>
                    <td>{{ $request->branch?->branch_name ?? $request->branch?->name }}</td>
                    <td>{{ number_format((float) $request->hours, 2) }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $request->status)) }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($request->reason, 40) }}</td>
                    <td>
                        <a href="{{ route('hr.overtime.edit', $request) }}" class="btn btn-xs btn-outline-primary">Edit</a>
                        @if(in_array($request->status, ['pending_manager','pending_hr'], true) && auth()->user()?->hasPermission('review_overtime_request'))
                        <form method="POST" action="{{ route('hr.overtime.approve', $request) }}" class="d-inline">@csrf<button class="btn btn-xs btn-outline-success">Approve</button></form>
                        <form method="POST" action="{{ route('hr.overtime.reject', $request) }}" class="d-inline">@csrf<button class="btn btn-xs btn-outline-danger">Reject</button></form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted">No overtime requests found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $overtimeRequests->links() }}</div>
</div>
@endsection
