@extends('layouts.app')

@section('page_title', 'Support Tickets')
@section('content')
<div class="card mb-3">
    <div class="card-body">
        <form method="POST" action="{{ route('deployment.support.store') }}" class="form-row align-items-end">@csrf
            <div class="col-md-2 col-sm-6 mb-2"><input name="module_name" class="form-control" placeholder="Module" required></div>
            <div class="col-md-2 col-sm-6 mb-2"><select name="priority" class="form-control"><option value="medium">Medium</option><option value="critical">Critical</option><option value="high">High</option><option value="low">Low</option></select></div>
            <div class="col-md-5 col-sm-12 mb-2"><input name="issue_description" class="form-control" placeholder="Issue description" required></div>
            <div class="col-md-2 col-sm-6 mb-2"><button class="btn btn-primary btn-block touch-btn">Submit Ticket</button></div>
        </form>
    </div>
</div>
<div class="card">
    <div class="table-responsive p-0">
        <table class="table table-sm mb-0">
            <thead><tr><th>Ticket #</th><th>Module</th><th>Priority</th><th>Status</th><th>Branch</th><th>Reported By</th><th>Assigned To</th><th>Issue</th></tr></thead>
            <tbody>
            @forelse($tickets as $ticket)
                <tr>
                    <td>{{ $ticket->ticket_number }}</td>
                    <td>{{ $ticket->module_name }}</td>
                    <td><span class="badge badge-{{ $ticket->priority === 'critical' ? 'danger' : ($ticket->priority === 'high' ? 'warning' : 'secondary') }}">{{ strtoupper($ticket->priority) }}</span></td>
                    <td><span class="badge badge-{{ $ticket->status === 'resolved' ? 'success' : ($ticket->status === 'open' ? 'secondary' : 'info') }}">{{ strtoupper($ticket->status) }}</span></td>
                    <td>{{ $ticket->branch?->branch_name ?? '-' }}</td>
                    <td>{{ $ticket->reporter?->display_name ?? '-' }}</td>
                    <td>{{ $ticket->assignee?->display_name ?? '-' }}</td>
                    <td>{{ $ticket->issue_description }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted">No support tickets yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $tickets->links() }}</div>
</div>
@endsection
