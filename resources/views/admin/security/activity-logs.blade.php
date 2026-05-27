@extends('layouts.app')

@section('page_title', 'Activity Logs')
@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard.owner') }}">Dashboard</a></li>
<li class="breadcrumb-item active">Activity Logs</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <a href="{{ route('admin.security.login-history') }}" class="btn btn-outline-info btn-sm">View Login History</a>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-striped table-hover text-nowrap">
            <thead><tr><th>Time</th><th>User</th><th>Branch</th><th>Module</th><th>Action</th><th>Description</th><th>IP</th></tr></thead>
            <tbody>
            @forelse($logs as $log)
                <tr>
                    <td>{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                    <td>{{ $log->user?->display_name ?? 'System' }}</td>
                    <td>{{ $log->branch?->name ?? '-' }}</td>
                    <td>{{ $log->module_name ?? $log->module }}</td>
                    <td>{{ $log->action_type ?? $log->action }}</td>
                    <td>{{ $log->description ?? '-' }}</td>
                    <td>{{ $log->ip_address ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center">No logs found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $logs->links() }}</div>
</div>
@endsection
