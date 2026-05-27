@extends('layouts.app')

@section('page_title', 'Wallet Details')
@section('content')
<div class="card mb-3">
    <div class="card-header">{{ $wallet->wallet_number }}</div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3"><strong>Branch:</strong> {{ $wallet->branch?->name }}</div>
            <div class="col-md-3"><strong>Provider:</strong> {{ $wallet->provider?->provider_name }}</div>
            <div class="col-md-3"><strong>Balance:</strong> {{ number_format($wallet->current_balance,2) }}</div>
            <div class="col-md-3"><strong>Status:</strong> {{ ucfirst($wallet->status) }}</div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Recent Ledger</div>
            <div class="card-body table-responsive p-0"><table class="table table-sm mb-0"><thead><tr><th>Type</th><th>In</th><th>Out</th><th>Balance</th><th>Date</th></tr></thead><tbody>
            @foreach($wallet->ledgers->take(20) as $ledger)
                <tr><td>{{ $ledger->movement_type }}</td><td>{{ $ledger->amount_in }}</td><td>{{ $ledger->amount_out }}</td><td>{{ $ledger->running_balance }}</td><td>{{ $ledger->created_at }}</td></tr>
            @endforeach
            </tbody></table></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Recent Transactions</div>
            <div class="card-body table-responsive p-0"><table class="table table-sm mb-0"><thead><tr><th>No</th><th>Mobile</th><th>Amount</th><th>Status</th></tr></thead><tbody>
            @foreach($wallet->transactions->take(20) as $tx)
                <tr><td>{{ $tx->transaction_number }}</td><td>{{ $tx->customer_mobile_number }}</td><td>{{ $tx->load_amount }}</td><td>{{ ucfirst($tx->transaction_status) }}</td></tr>
            @endforeach
            </tbody></table></div>
        </div>
    </div>
</div>
@endsection
