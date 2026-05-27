@extends('layouts.app')

@section('page_title', 'Wallet Funding')
@section('content')
<div class="card mb-3">
    <div class="card-header">Request Funding</div>
    <div class="card-body">
        <form method="POST" action="{{ route('airtime.fundings.store') }}" enctype="multipart/form-data" class="form-row">
            @csrf
            <div class="col-md-3"><label>Wallet</label><select class="form-control" name="wallet_id">@foreach($wallets as $wallet)<option value="{{ $wallet->id }}">{{ $wallet->wallet_number }} - {{ $wallet->branch?->name }} - {{ $wallet->provider?->provider_name }}</option>@endforeach</select></div>
            <div class="col-md-2"><label>Amount</label><input class="form-control" type="number" step="0.01" name="amount" required></div>
            <div class="col-md-2"><label>Date</label><input class="form-control" type="date" name="funding_date" value="{{ now()->toDateString() }}" required></div>
            <div class="col-md-2"><label>Payment Method</label><input class="form-control" name="payment_method" required></div>
            <div class="col-md-3"><label>Reference</label><input class="form-control" name="reference_number"></div>
            <div class="col-md-4 mt-2"><label>Proof</label><input class="form-control-file" type="file" name="proof_file"></div>
            <div class="col-md-6 mt-2"><label>Remarks</label><input class="form-control" name="remarks"></div>
            <div class="col-md-2 mt-4"><button class="btn btn-primary btn-block">Submit</button></div>
        </form>
    </div>
</div>

<div class="card"><div class="card-body table-responsive p-0"><table class="table table-sm mb-0"><thead><tr><th>No</th><th>Wallet</th><th>Branch</th><th>Provider</th><th>Amount</th><th>Status</th><th>Requested By</th><th></th></tr></thead><tbody>
@foreach($fundings as $funding)
<tr><td>{{ $funding->funding_number }}</td><td>{{ $funding->wallet?->wallet_number }}</td><td>{{ $funding->branch?->name }}</td><td>{{ $funding->provider?->provider_name }}</td><td>{{ number_format($funding->amount,2) }}</td><td>{{ ucfirst($funding->status) }}</td><td>{{ $funding->requester?->display_name }}</td><td>
@if($funding->status==='pending')
<form method="POST" action="{{ route('airtime.fundings.approve',$funding) }}" class="d-inline">@csrf<button class="btn btn-xs btn-success">Approve</button></form>
<form method="POST" action="{{ route('airtime.fundings.reject',$funding) }}" class="d-inline">@csrf<input type="hidden" name="rejection_reason" value="Rejected by approver"><button class="btn btn-xs btn-danger">Reject</button></form>
@endif
</td></tr>
@endforeach
</tbody></table></div><div class="card-footer">{{ $fundings->links() }}</div></div>
@endsection
