@extends('layouts.app')

@section('page_title', 'File Access Logs')
@section('content')
<div class="card"><div class="table-responsive p-0"><table class="table table-sm mb-0"><thead><tr><th>Time</th><th>User</th><th>Module</th><th>File</th><th>Status</th><th>IP</th></tr></thead><tbody>@forelse($logs as $log)<tr><td>{{ $log->accessed_at?->format('Y-m-d H:i:s') }}</td><td>{{ $log->user?->display_name ?? '-' }}</td><td>{{ $log->module_name }}</td><td>{{ $log->file_name }}</td><td>{{ strtoupper($log->status) }}</td><td>{{ $log->ip_address }}</td></tr>@empty<tr><td colspan="6" class="text-center text-muted">No file access logs.</td></tr>@endforelse</tbody></table></div><div class="card-footer">{{ $logs->links() }}</div></div>
@endsection
