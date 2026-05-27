@extends('layouts.app')

@section('page_title', 'Airtime Wallets')
@section('content')
<div class="card mb-3"><div class="card-body">
<form method="GET" class="form-row">
    <div class="col-md-3"><select class="form-control" name="branch_id"><option value="">Branch</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected(($filters['branch_id'] ?? null)==$branch->id)>{{ $branch->name }}</option>@endforeach</select></div>
    <div class="col-md-3"><select class="form-control" name="provider_id"><option value="">Provider</option>@foreach($providers as $provider)<option value="{{ $provider->id }}" @selected(($filters['provider_id'] ?? null)==$provider->id)>{{ $provider->provider_name }}</option>@endforeach</select></div>
    <div class="col-md-2"><select class="form-control" name="status"><option value="">Status</option><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
    <div class="col-md-2"><button class="btn btn-outline-primary btn-block">Filter</button></div>
</form></div></div>

<div class="card mb-3">
    <div class="card-header">Create Wallet</div>
    <div class="card-body">
        <form method="POST" action="{{ route('airtime.wallets.store') }}" class="form-row">
            @csrf
            <div class="col-md-2"><input class="form-control" name="wallet_number" placeholder="Wallet #" required></div>
            <div class="col-md-2"><select class="form-control" name="branch_id">@foreach($branches as $branch)<option value="{{ $branch->id }}">{{ $branch->name }}</option>@endforeach</select></div>
            <div class="col-md-2"><select class="form-control" name="provider_id">@foreach($providers as $provider)<option value="{{ $provider->id }}">{{ $provider->provider_name }}</option>@endforeach</select></div>
            <div class="col-md-2"><input class="form-control" type="number" step="0.01" name="beginning_balance" placeholder="Beginning" required></div>
            <div class="col-md-2"><input class="form-control" type="number" step="0.01" name="low_balance_threshold" placeholder="Low Threshold" required></div>
            <div class="col-md-1"><select class="form-control" name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
            <div class="col-md-1"><button class="btn btn-primary btn-block">Save</button></div>
        </form>
    </div>
</div>

<div class="card"><div class="card-body table-responsive p-0"><table class="table table-sm mb-0"><thead><tr><th>Wallet</th><th>Branch</th><th>Provider</th><th>Current</th><th>Threshold</th><th>Status</th><th></th></tr></thead><tbody>
@foreach($wallets as $wallet)
<tr><td>{{ $wallet->wallet_number }}</td><td>{{ $wallet->branch?->name }}</td><td>{{ $wallet->provider?->provider_name }}</td><td>{{ number_format($wallet->current_balance,2) }}</td><td>{{ number_format($wallet->low_balance_threshold,2) }}</td><td>{{ ucfirst($wallet->status) }}</td><td><a class="btn btn-xs btn-outline-secondary" href="{{ route('airtime.wallets.show',$wallet) }}">View</a></td></tr>
@endforeach
</tbody></table></div><div class="card-footer">{{ $wallets->links() }}</div></div>
@endsection
