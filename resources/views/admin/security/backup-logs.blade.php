@extends('layouts.app')

@section('page_title', 'Backup Logs')
@section('content')
<div class="card mb-3"><div class="card-header">Run Manual Backup Readiness</div><div class="card-body"><form method="POST" action="{{ route('admin.security.backup-logs.run') }}" class="form-row">@csrf<div class="col-md-10 mb-2"><input class="form-control" name="remarks" placeholder="Remarks"></div><div class="col-md-2 mb-2"><button class="btn btn-primary btn-block">Run</button></div></form></div></div>
<div class="card"><div class="table-responsive p-0"><table class="table table-sm mb-0"><thead><tr><th>Backup #</th><th>Type</th><th>Status</th><th>Started By</th><th>Started At</th><th>Completed At</th></tr></thead><tbody>@forelse($logs as $log)<tr><td>{{ $log->backup_number }}</td><td>{{ strtoupper($log->backup_type) }}</td><td>{{ strtoupper($log->status) }}</td><td>{{ $log->starter?->display_name ?? '-' }}</td><td>{{ $log->started_at }}</td><td>{{ $log->completed_at }}</td></tr>@empty<tr><td colspan="6" class="text-center text-muted">No backup logs.</td></tr>@endforelse</tbody></table></div><div class="card-footer">{{ $logs->links() }}</div></div>
@endsection
