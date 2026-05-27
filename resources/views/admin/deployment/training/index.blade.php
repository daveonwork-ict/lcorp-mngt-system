@extends('layouts.app')

@section('page_title', 'Training Logs')
@section('content')
<div class="card mb-3">
    <div class="card-body">
        <form method="POST" action="{{ route('deployment.training.store') }}" class="form-row align-items-end">@csrf
            <div class="col-md-3 mb-2"><input name="training_group" class="form-control" placeholder="Training group" required></div>
            <div class="col-md-3 mb-2"><input name="session_title" class="form-control" placeholder="Session title" required></div>
            <div class="col-md-2 mb-2"><input name="attendee_name" class="form-control" placeholder="Attendee name" required></div>
            <div class="col-md-2 mb-2"><input name="attendee_role" class="form-control" placeholder="Role" required></div>
            <div class="col-md-2 mb-2"><button class="btn btn-primary btn-block touch-btn">Save Training</button></div>
            <div class="col-md-12"><small class="text-muted">Use this for actual training attendance and rollout notes.</small></div>
        </form>
    </div>
</div>
<div class="card">
    <div class="table-responsive p-0">
        <table class="table table-sm mb-0">
            <thead><tr><th>Training #</th><th>Group</th><th>Session</th><th>Branch</th><th>Attendee</th><th>Role</th><th>Status</th><th>Recorded By</th></tr></thead>
            <tbody>
            @forelse($logs as $log)
                <tr>
                    <td>{{ $log->training_number }}</td>
                    <td>{{ $log->training_group }}</td>
                    <td>{{ $log->session_title }}</td>
                    <td>{{ $log->branch?->branch_name ?? '-' }}</td>
                    <td>{{ $log->attendee_name }}</td>
                    <td>{{ $log->attendee_role }}</td>
                    <td><span class="badge badge-{{ $log->status === 'completed' ? 'success' : ($log->status === 'cancelled' ? 'danger' : 'warning') }}">{{ strtoupper($log->status) }}</span></td>
                    <td>{{ $log->recorder?->display_name ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted">No training logs yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $logs->links() }}</div>
</div>
@endsection
