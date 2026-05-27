@extends('layouts.app')

@section('page_title', 'Login History')
@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard.owner') }}">Dashboard</a></li>
<li class="breadcrumb-item"><a href="{{ route('admin.activity-logs.index') }}">Activity Logs</a></li>
<li class="breadcrumb-item active">Login History</li>
@endsection

@section('content')
<div class="card">
    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
            <thead><tr><th>Time</th><th>User</th><th>Action</th><th>IP</th><th>User Agent</th></tr></thead>
            <tbody>
            @forelse($logs as $log)
                <tr>
                    <td>{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                    <td>{{ $log->user?->display_name ?? 'Unknown' }}</td>
                    <td>{{ strtoupper($log->action_type ?? $log->action) }}</td>
                    <td>{{ $log->ip_address }}</td>
                    <td class="text-wrap" style="white-space:normal">{{ $log->user_agent }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center">No login history yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $logs->links() }}</div>
</div>
@endsection
