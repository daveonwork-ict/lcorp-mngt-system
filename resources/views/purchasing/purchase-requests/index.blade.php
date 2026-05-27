@extends('layouts.app')

@section('page_title', 'Purchase Requests')
@section('content')
<div class="card mb-3">
    <div class="card-header">Create Purchase Request</div>
    <div class="card-body">
        <form method="POST" action="{{ route('purchasing.requests.store') }}" class="form-row">@csrf
            <div class="col-md-2 mb-2"><select name="branch_id" class="form-control" required><option value="">Branch</option>@foreach($branches as $branch)<option value="{{ $branch->id }}">{{ $branch->branch_name ?? $branch->name }}</option>@endforeach</select></div>
            <div class="col-md-2 mb-2"><input type="date" name="request_date" class="form-control" value="{{ now()->toDateString() }}" required></div>
            <div class="col-md-2 mb-2"><select name="priority" class="form-control" required><option value="normal">Normal</option><option value="high">High</option><option value="urgent">Urgent</option></select></div>
            <div class="col-md-3 mb-2"><input name="purpose" class="form-control" placeholder="Purpose" required></div>
            <div class="col-md-2 mb-2"><select name="items[0][product_id]" class="form-control" required><option value="">Product</option>@foreach($products as $product)<option value="{{ $product->id }}">{{ $product->product_name }}</option>@endforeach</select></div>
            <div class="col-md-1 mb-2"><input type="number" min="1" name="items[0][requested_quantity]" class="form-control" placeholder="Qty" required></div>
            <div class="col-md-2 mb-2"><input type="number" step="0.01" min="0" name="items[0][estimated_cost]" class="form-control" placeholder="Est. cost"></div>
            <div class="col-md-2 mb-2"><button class="btn btn-success btn-block">Submit</button></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">Request Log</div>
    <div class="table-responsive p-0">
        <table class="table table-sm mb-0">
            <thead><tr><th>Request #</th><th>Branch</th><th>Date</th><th>Priority</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($requests as $row)
                <tr>
                    <td>{{ $row->request_number }}</td><td>{{ $row->branch?->branch_name ?? $row->branch?->name }}</td><td>{{ $row->request_date }}</td><td>{{ ucfirst($row->priority) }}</td><td>{{ ucfirst(str_replace('_', ' ', $row->status)) }}</td>
                    <td>
                        @if($row->status === 'pending_approval')
                            <form method="POST" action="{{ route('purchasing.requests.approve', $row) }}" class="d-inline">@csrf<button class="btn btn-sm btn-outline-success">Approve</button></form>
                            <form method="POST" action="{{ route('purchasing.requests.reject', $row) }}" class="d-inline">@csrf<input type="hidden" name="rejection_reason" value="Rejected by approver"><button class="btn btn-sm btn-outline-danger">Reject</button></form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted">No purchase requests found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $requests->links() }}</div>
</div>
@endsection
