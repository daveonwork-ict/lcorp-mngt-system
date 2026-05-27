@extends('layouts.app')

@section('page_title', 'Receiving Reports')
@section('content')
<div class="card mb-3">
    <div class="card-header">Post Receiving</div>
    <div class="card-body">
        <form method="POST" action="{{ route('purchasing.receiving-reports.store') }}" enctype="multipart/form-data" class="form-row">@csrf
            <div class="col-md-3 mb-2"><select name="purchase_order_id" class="form-control" required><option value="">PO</option>@foreach($orders as $order)<option value="{{ $order->id }}">{{ $order->po_number }}</option>@endforeach</select></div>
            <div class="col-md-2 mb-2"><input type="date" name="received_date" class="form-control" value="{{ now()->toDateString() }}" required></div>
            <div class="col-md-2 mb-2"><input name="delivery_receipt_number" class="form-control" placeholder="DR #"></div>
            <div class="col-md-2 mb-2"><input name="invoice_number" class="form-control" placeholder="Invoice #"></div>
            <div class="col-md-2 mb-2"><input type="file" name="attachment" class="form-control-file"></div>
            <div class="col-md-1 mb-2"><button class="btn btn-primary btn-block">Post</button></div>
            <div class="col-md-3 mb-2"><input type="number" min="1" name="items[0][product_id]" class="form-control" placeholder="Product ID" required></div>
            <div class="col-md-2 mb-2"><input type="number" min="1" name="items[0][quantity_received]" class="form-control" placeholder="Qty" required></div>
            <div class="col-md-2 mb-2"><input type="number" step="0.01" min="0" name="items[0][unit_cost]" class="form-control" placeholder="Unit cost"></div>
        </form>
    </div>
</div>

<div class="card"><div class="card-header">Receiving Register</div><div class="table-responsive p-0"><table class="table table-sm mb-0"><thead><tr><th>RR #</th><th>PO #</th><th>Supplier</th><th>Date</th><th>Attachment</th></tr></thead><tbody>@forelse($reports as $row)<tr><td>{{ $row->receiving_number }}</td><td>{{ $row->purchaseOrder?->po_number }}</td><td>{{ $row->supplier?->supplier_name }}</td><td>{{ $row->received_date }}</td><td>@if($row->attachment_path)<a href="{{ route('purchasing.receiving-reports.download', $row) }}" class="btn btn-sm btn-outline-secondary">Download</a>@endif</td></tr>@empty<tr><td colspan="5" class="text-center text-muted">No receiving records.</td></tr>@endforelse</tbody></table></div><div class="card-footer">{{ $reports->links() }}</div></div>
@endsection
