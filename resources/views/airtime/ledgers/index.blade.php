@extends('layouts.app')

@section('page_title', 'Wallet Ledger')
@section('content')
<div class="card mb-3"><div class="card-body"><form method="GET" class="form-row"><div class="col-md-2"><input class="form-control" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}"></div><div class="col-md-2"><input class="form-control" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}"></div><div class="col-md-3"><select class="form-control" name="branch_id"><option value="">Branch</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected(($filters['branch_id'] ?? null)==$branch->id)>{{ $branch->name }}</option>@endforeach</select></div><div class="col-md-3"><select class="form-control" name="provider_id"><option value="">Provider</option>@foreach($providers as $provider)<option value="{{ $provider->id }}" @selected(($filters['provider_id'] ?? null)==$provider->id)>{{ $provider->provider_name }}</option>@endforeach</select></div><div class="col-md-2"><button class="btn btn-outline-primary btn-block">Filter</button></div></form></div></div>
<div class="card"><div class="card-body table-responsive p-0"><table class="table table-sm mb-0"><thead><tr><th>Date</th><th>Wallet</th><th>Branch</th><th>Provider</th><th>Type</th><th>In</th><th>Out</th><th>Balance</th><th>Ref</th></tr></thead><tbody>
@foreach($ledgers as $ledger)
<tr><td>{{ $ledger->created_at }}</td><td>{{ $ledger->wallet?->wallet_number }}</td><td>{{ $ledger->branch?->name }}</td><td>{{ $ledger->provider?->provider_name }}</td><td>{{ $ledger->movement_type }}</td><td>{{ $ledger->amount_in }}</td><td>{{ $ledger->amount_out }}</td><td>{{ $ledger->running_balance }}</td><td>{{ $ledger->reference_type }} #{{ $ledger->reference_id }}</td></tr>
@endforeach
</tbody></table></div><div class="card-footer">{{ $ledgers->links() }}</div></div>
@endsection
