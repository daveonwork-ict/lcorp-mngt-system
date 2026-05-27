@extends('layouts.app')

@section('page_title', 'Supplier Payables')
@section('content')
<div class="row mb-3">
    <div class="col-md-4"><div class="card"><div class="card-body"><small>Current</small><div class="h5 mb-0">{{ number_format((float) $aging['current'], 2) }}</div></div></div></div>
    <div class="col-md-4"><div class="card"><div class="card-body"><small>1-30 Days Overdue</small><div class="h5 mb-0">{{ number_format((float) $aging['over_1_30'], 2) }}</div></div></div></div>
    <div class="col-md-4"><div class="card"><div class="card-body"><small>Over 30 Days</small><div class="h5 mb-0">{{ number_format((float) $aging['over_30'], 2) }}</div></div></div></div>
</div>

<div class="card"><div class="card-header">Payable Listing</div><div class="table-responsive p-0"><table class="table table-sm mb-0"><thead><tr><th>Payable #</th><th>Supplier</th><th>Due Date</th><th>Total</th><th>Paid</th><th>Balance</th><th>Status</th></tr></thead><tbody>@forelse($payables as $row)<tr><td>{{ $row->payable_number }}</td><td>{{ $row->supplier?->supplier_name }}</td><td>{{ $row->due_date }}</td><td>{{ number_format((float) $row->total_amount, 2) }}</td><td>{{ number_format((float) $row->amount_paid, 2) }}</td><td>{{ number_format((float) $row->balance_amount, 2) }}</td><td>{{ ucfirst($row->payment_status) }}</td></tr>@empty<tr><td colspan="7" class="text-center text-muted">No payables found.</td></tr>@endforelse</tbody></table></div><div class="card-footer">{{ $payables->links() }}</div></div>
@endsection
