@extends('layouts.app')

@section('page_title', 'Approval Inbox')
@section('content')
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="form-row">
            <div class="col-md-3 mb-2"><input class="form-control" name="module_name" placeholder="Module" value="{{ $filters['module_name'] ?? '' }}"></div>
            <div class="col-md-3 mb-2"><select class="form-control" name="priority"><option value="">Priority</option><option value="normal">Normal</option><option value="important">Important</option><option value="urgent">Urgent</option><option value="critical">Critical</option></select></div>
            <div class="col-md-3 mb-2"><select class="form-control" name="status"><option value="">Status</option><option value="pending">Pending</option><option value="under_review">Under Review</option><option value="approved">Approved</option><option value="rejected">Rejected</option><option value="returned_for_correction">Returned</option></select></div>
            <div class="col-md-3 mb-2"><button class="btn btn-primary btn-block">Filter</button></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive p-0">
        <table class="table table-sm table-hover mb-0">
            <thead><tr><th>Approval #</th><th>Module</th><th>Requester</th><th>Branch</th><th>Priority</th><th>Status</th><th>Current Approver</th><th></th></tr></thead>
            <tbody>
            @forelse($inbox as $item)
                <tr>
                    <td>{{ $item->approval_number }}</td>
                    <td>{{ ucfirst($item->module_name) }}</td>
                    <td>{{ $item->requester?->display_name }}</td>
                    <td>{{ $item->branch?->branch_name ?? $item->branch?->name ?? '-' }}</td>
                    <td><span class="badge badge-{{ in_array($item->priority, ['urgent','critical']) ? 'danger' : 'info' }}">{{ strtoupper($item->priority) }}</span></td>
                    <td><span class="badge badge-secondary">{{ strtoupper(str_replace('_', ' ', $item->status)) }}</span></td>
                    <td>{{ $item->currentApprover?->display_name ?? '-' }}</td>
                    <td><a href="{{ route('approvals.requests.show', $item) }}" class="btn btn-sm btn-outline-primary">Open</a></td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted">No approval records found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $inbox->links() }}</div>
</div>
@endsection
