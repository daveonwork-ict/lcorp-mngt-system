@extends('layouts.app')

@section('page_title', 'Airtime Commissions')
@section('content')
<div class="card mb-3"><div class="card-body"><form method="GET" class="form-row"><div class="col-md-2"><input class="form-control" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}"></div><div class="col-md-2"><input class="form-control" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}"></div><div class="col-md-3"><select class="form-control" name="branch_id"><option value="">Branch</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected(($filters['branch_id'] ?? null)==$branch->id)>{{ $branch->name }}</option>@endforeach</select></div><div class="col-md-3"><select class="form-control" name="provider_id"><option value="">Provider</option>@foreach($providers as $provider)<option value="{{ $provider->id }}" @selected(($filters['provider_id'] ?? null)==$provider->id)>{{ $provider->provider_name }}</option>@endforeach</select></div><div class="col-md-2"><button class="btn btn-outline-primary btn-block">Filter</button></div></form></div></div>
<div class="card"><div class="card-body table-responsive p-0"><table class="table table-sm mb-0"><thead><tr><th>Date</th><th>Transaction</th><th>Branch</th><th>Provider</th><th>Type</th><th>Value</th><th>Amount</th></tr></thead><tbody>
@foreach($commissions as $commission)
<tr><td>{{ $commission->created_at }}</td><td>{{ $commission->transaction?->transaction_number }}</td><td>{{ $commission->branch?->name }}</td><td>{{ $commission->provider?->provider_name }}</td><td>{{ ucfirst($commission->commission_type) }}</td><td>{{ $commission->commission_value }}</td><td>{{ number_format($commission->commission_amount,2) }}</td></tr>
@endforeach
</tbody></table></div><div class="card-footer">{{ $commissions->links() }}</div></div>
@endsection
