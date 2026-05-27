@extends('layouts.app')

@section('page_title', 'Purchase Orders')
@section('content')
<div class="card mb-3">
    <div class="card-header">Create Purchase Order</div>
    <div class="card-body">
        <form method="POST" action="{{ route('purchasing.orders.store') }}" class="form-row">@csrf
            <div class="col-md-2 mb-2"><select name="supplier_id" class="form-control" required><option value="">Supplier</option>@foreach($suppliers as $supplier)<option value="{{ $supplier->id }}">{{ $supplier->supplier_name }}</option>@endforeach</select></div>
            <div class="col-md-2 mb-2"><select name="branch_id" class="form-control" required><option value="">Branch</option>@foreach($branches as $branch)<option value="{{ $branch->id }}">{{ $branch->branch_name ?? $branch->name }}</option>@endforeach</select></div>
            <div class="col-md-2 mb-2"><select name="request_id" class="form-control"><option value="">From approved PR</option>@foreach($requests as $pr)<option value="{{ $pr->id }}">{{ $pr->request_number }}</option>@endforeach</select></div>
            <div class="col-md-2 mb-2"><input type="date" name="po_date" class="form-control" value="{{ now()->toDateString() }}" required></div>
            <div class="col-md-2 mb-2"><input type="date" name="expected_delivery_date" class="form-control"></div>
            <div class="col-md-2 mb-2"><button class="btn btn-primary btn-block">Create</button></div>
            <div class="col-md-3 mb-2"><select name="items[0][product_id]" class="form-control"><option value="">Manual item product</option>@foreach($products as $product)<option value="{{ $product->id }}">{{ $product->product_name }}</option>@endforeach</select></div>
            <div class="col-md-1 mb-2"><input type="number" name="items[0][quantity_ordered]" min="1" class="form-control" placeholder="Qty"></div>
            <div class="col-md-2 mb-2"><input type="number" name="items[0][unit_cost]" step="0.01" min="0" class="form-control" placeholder="Unit cost"></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">PO Register</div>
    <div class="table-responsive p-0"><table class="table table-sm mb-0"><thead><tr><th>PO #</th><th>Supplier</th><th>Date</th><th>Status</th><th>Total</th><th></th></tr></thead><tbody>@forelse($orders as $row)<tr><td>{{ $row->po_number }}</td><td>{{ $row->supplier?->supplier_name }}</td><td>{{ $row->po_date }}</td><td>{{ ucfirst(str_replace('_', ' ', $row->status)) }}</td><td>{{ number_format((float) $row->total_amount, 2) }}</td><td>@if($row->status === 'pending_approval')<form method="POST" action="{{ route('purchasing.orders.approve', $row) }}" class="d-inline">@csrf<button class="btn btn-sm btn-outline-success">Approve</button></form>@endif @if(in_array($row->status, ['approved', 'sent']))<form method="POST" action="{{ route('purchasing.orders.send', $row) }}" class="d-inline">@csrf<button class="btn btn-sm btn-outline-primary">Mark Sent</button></form>@endif</td></tr>@empty<tr><td colspan="6" class="text-center text-muted">No purchase orders found.</td></tr>@endforelse</tbody></table></div>
    <div class="card-footer">{{ $orders->links() }}</div>
</div>
@endsection
