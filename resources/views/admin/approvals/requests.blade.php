@extends('layouts.app')

@section('page_title', 'Approval Requests')
@section('content')
<div class="card mb-3">
    <div class="card-header">Create Approval Request</div>
    <div class="card-body">
        <form method="POST" action="{{ route('approvals.requests.store') }}" class="form-row">@csrf
            <div class="col-md-2 mb-2"><input class="form-control" name="module_name" placeholder="Module" required></div>
            <div class="col-md-2 mb-2"><input class="form-control" name="transaction_type" placeholder="Transaction type"></div>
            <div class="col-md-2 mb-2"><input class="form-control" name="reference_type" placeholder="Reference type" required></div>
            <div class="col-md-2 mb-2"><input type="number" min="1" class="form-control" name="reference_id" placeholder="Reference ID" required></div>
            <div class="col-md-2 mb-2"><select class="form-control" name="branch_id"><option value="">Branch</option>@foreach($branches as $branch)<option value="{{ $branch->id }}">{{ $branch->branch_name ?? $branch->name }}</option>@endforeach</select></div>
            <div class="col-md-2 mb-2"><input type="number" min="0" step="0.01" class="form-control" name="amount" placeholder="Amount"></div>
            <div class="col-md-2 mb-2"><select class="form-control" name="priority"><option value="normal">Normal</option><option value="important">Important</option><option value="urgent">Urgent</option><option value="critical">Critical</option></select></div>
            <div class="col-md-6 mb-2"><input class="form-control" name="remarks" placeholder="Remarks"></div>
            <div class="col-md-2 mb-2"><button class="btn btn-primary btn-block">Submit</button></div>
        </form>
    </div>
</div>

<div class="card"><div class="table-responsive p-0"><table class="table table-sm mb-0"><thead><tr><th>Approval #</th><th>Module</th><th>Status</th><th>Priority</th><th>Current Approver</th><th></th></tr></thead><tbody>@forelse($requests as $item)<tr><td>{{ $item->approval_number }}</td><td>{{ $item->module_name }}</td><td>{{ strtoupper(str_replace('_', ' ', $item->status)) }}</td><td>{{ strtoupper($item->priority) }}</td><td>{{ $item->currentApprover?->display_name ?? '-' }}</td><td><a class="btn btn-sm btn-outline-primary" href="{{ route('approvals.requests.show', $item) }}">View</a></td></tr>@empty<tr><td colspan="6" class="text-center text-muted">No requests.</td></tr>@endforelse</tbody></table></div><div class="card-footer">{{ $requests->links() }}</div></div>
@endsection
