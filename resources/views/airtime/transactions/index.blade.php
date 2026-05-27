@extends('layouts.app')

@section('page_title', 'Airtime Transactions')
@section('content')
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="form-row">
            <div class="col-md-2"><input class="form-control" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}"></div>
            <div class="col-md-2"><input class="form-control" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}"></div>
            <div class="col-md-2"><select class="form-control" name="branch_id"><option value="">Branch</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected(($filters['branch_id'] ?? null)==$branch->id)>{{ $branch->name }}</option>@endforeach</select></div>
            <div class="col-md-2"><select class="form-control" name="provider_id"><option value="">Provider</option>@foreach($providers as $provider)<option value="{{ $provider->id }}" @selected(($filters['provider_id'] ?? null)==$provider->id)>{{ $provider->provider_name }}</option>@endforeach</select></div>
            <div class="col-md-2"><select class="form-control" name="status"><option value="">Status</option><option value="successful">Successful</option><option value="pending">Pending</option><option value="failed">Failed</option><option value="cancelled">Cancelled</option><option value="reversed">Reversed</option></select></div>
            <div class="col-md-2"><button class="btn btn-outline-primary btn-block">Filter</button></div>
        </form>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">Create Load Transaction</div>
    <div class="card-body">
        <form method="POST" action="{{ route('airtime.transactions.store') }}" class="form-row">
            @csrf
            <div class="col-md-2"><label>Branch</label><select class="form-control" name="branch_id">@foreach($branches as $branch)<option value="{{ $branch->id }}">{{ $branch->name }}</option>@endforeach</select></div>
            <div class="col-md-2"><label>Provider</label><select class="form-control" name="provider_id">@foreach($providers as $provider)<option value="{{ $provider->id }}">{{ $provider->provider_name }}</option>@endforeach</select></div>
            <div class="col-md-2"><label>Wallet</label><select class="form-control" name="wallet_id">@foreach($wallets as $wallet)<option value="{{ $wallet->id }}">{{ $wallet->wallet_number }}</option>@endforeach</select></div>
            <div class="col-md-2"><label>Mobile</label><input class="form-control" name="customer_mobile_number" placeholder="09xxxxxxxxx" required></div>
            <div class="col-md-1"><label>Amount</label><input class="form-control" type="number" step="0.01" name="load_amount" required></div>
            <div class="col-md-2"><label>Payment</label><select class="form-control" name="payment_method_id"><option value="">None</option>@foreach($paymentMethods as $method)<option value="{{ $method->id }}">{{ $method->payment_method_name }}</option>@endforeach</select></div>
            <div class="col-md-1"><label>Status</label><select class="form-control" name="transaction_status"><option value="successful">Success</option><option value="pending">Pending</option><option value="failed">Failed</option><option value="cancelled">Cancelled</option></select></div>
            <div class="col-md-3 mt-2"><label>Reference</label><input class="form-control" name="payment_reference"></div>
            <div class="col-md-7 mt-2"><label>Remarks</label><input class="form-control" name="remarks"></div>
            <div class="col-md-2 mt-4"><button class="btn btn-primary btn-block">Process</button></div>
        </form>
    </div>
</div>

<div class="card"><div class="card-body table-responsive p-0"><table class="table table-sm mb-0"><thead><tr><th>No</th><th>Branch</th><th>Provider</th><th>Mobile</th><th>Load</th><th>Commission</th><th>Status</th><th>Processed</th><th></th></tr></thead><tbody>
@foreach($transactions as $tx)
<tr><td>{{ $tx->transaction_number }}</td><td>{{ $tx->branch?->name }}</td><td>{{ $tx->provider?->provider_name }}</td><td>{{ $tx->customer_mobile_number }}</td><td>{{ number_format($tx->load_amount,2) }}</td><td>{{ number_format($tx->commission_amount,2) }}</td><td>{{ ucfirst($tx->transaction_status) }}</td><td>{{ $tx->processed_at }}</td><td><a class="btn btn-xs btn-outline-secondary" href="{{ route('airtime.transactions.show',$tx) }}">View</a></td></tr>
@endforeach
</tbody></table></div><div class="card-footer">{{ $transactions->links() }}</div></div>
@endsection
