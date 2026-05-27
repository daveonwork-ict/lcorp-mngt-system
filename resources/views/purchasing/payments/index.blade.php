@extends('layouts.app')

@section('page_title', 'Supplier Payments')
@section('content')
<div class="card mb-3">
    <div class="card-header">Record Payment</div>
    <div class="card-body">
        <form method="POST" action="{{ route('purchasing.payments.store') }}" enctype="multipart/form-data" class="form-row">@csrf
            <div class="col-md-3 mb-2"><select name="payable_id" class="form-control" required><option value="">Payable</option>@foreach($payables as $payable)<option value="{{ $payable->id }}">{{ $payable->payable_number }} - {{ $payable->supplier?->supplier_name }}</option>@endforeach</select></div>
            <div class="col-md-2 mb-2"><input type="date" name="payment_date" class="form-control" value="{{ now()->toDateString() }}" required></div>
            <div class="col-md-2 mb-2"><select name="payment_method_id" class="form-control"><option value="">Method</option>@foreach($paymentMethods as $method)<option value="{{ $method->id }}">{{ $method->payment_method_name }}</option>@endforeach</select></div>
            <div class="col-md-2 mb-2"><input type="number" step="0.01" min="0.01" name="amount_paid" class="form-control" placeholder="Amount" required></div>
            <div class="col-md-2 mb-2"><input type="file" name="proof_file" class="form-control-file"></div>
            <div class="col-md-1 mb-2"><button class="btn btn-success btn-block">Post</button></div>
        </form>
    </div>
</div>

<div class="card"><div class="card-header">Payment Log</div><div class="table-responsive p-0"><table class="table table-sm mb-0"><thead><tr><th>Payment #</th><th>Supplier</th><th>Date</th><th>Amount</th><th>Reference</th><th>Proof</th></tr></thead><tbody>@forelse($payments as $row)<tr><td>{{ $row->payment_number }}</td><td>{{ $row->supplier?->supplier_name }}</td><td>{{ $row->payment_date }}</td><td>{{ number_format((float) $row->amount_paid, 2) }}</td><td>{{ $row->reference_number }}</td><td>@if($row->proof_file)<a href="{{ route('purchasing.payments.proof', $row) }}" class="btn btn-sm btn-outline-secondary">Download</a>@endif</td></tr>@empty<tr><td colspan="6" class="text-center text-muted">No payments found.</td></tr>@endforelse</tbody></table></div><div class="card-footer">{{ $payments->links() }}</div></div>
@endsection
