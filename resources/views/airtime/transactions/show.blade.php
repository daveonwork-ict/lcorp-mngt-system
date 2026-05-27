@extends('layouts.app')

@section('page_title', 'Airtime Transaction Details')
@section('content')
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between">
        <strong>{{ $transaction->transaction_number }}</strong>
        @if(in_array($transaction->transaction_status,['successful','pending']))
            <form method="POST" action="{{ route('airtime.transactions.reverse',$transaction) }}" class="form-inline">@csrf<input class="form-control form-control-sm mr-1" name="reversal_reason" placeholder="Reversal reason" required><button class="btn btn-sm btn-danger">Reverse</button></form>
        @endif
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3"><strong>Branch:</strong> {{ $transaction->branch?->name }}</div>
            <div class="col-md-3"><strong>Provider:</strong> {{ $transaction->provider?->provider_name }}</div>
            <div class="col-md-3"><strong>Wallet:</strong> {{ $transaction->wallet?->wallet_number }}</div>
            <div class="col-md-3"><strong>Cashier:</strong> {{ $transaction->cashier?->display_name }}</div>
            <div class="col-md-3 mt-2"><strong>Mobile:</strong> {{ $transaction->customer_mobile_number }}</div>
            <div class="col-md-3 mt-2"><strong>Load Amount:</strong> {{ number_format($transaction->load_amount,2) }}</div>
            <div class="col-md-3 mt-2"><strong>Commission:</strong> {{ number_format($transaction->commission_amount,2) }}</div>
            <div class="col-md-3 mt-2"><strong>Status:</strong> {{ ucfirst($transaction->transaction_status) }}</div>
        </div>
        <hr>
        <p><strong>Payment Method:</strong> {{ $transaction->paymentMethod?->payment_method_name }}</p>
        <p><strong>Payment Reference:</strong> {{ $transaction->payment_reference }}</p>
        <p><strong>Remarks:</strong> {{ $transaction->remarks }}</p>
        <p><strong>Reversal Reason:</strong> {{ $transaction->reversal_reason }}</p>
    </div>
</div>
@endsection
