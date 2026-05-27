@extends('layouts.app')

@section('page_title', 'Opening Cash')
@section('content')
<div class="card mb-3"><div class="card-header">New Opening</div><div class="card-body">
<form method="POST" action="{{ route('finance.openings.store') }}" class="form-row">@csrf
<div class="col-md-3"><label>Branch</label><select class="form-control" name="branch_id" required>@foreach($branches as $branch)<option value="{{ $branch->id }}">{{ $branch->name }}</option>@endforeach</select></div>
<div class="col-md-3"><label>Cashier</label><select class="form-control" name="cashier_id" required>@foreach($cashiers as $cashier)<option value="{{ $cashier->id }}">{{ $cashier->display_name }}</option>@endforeach</select></div>
<div class="col-md-2"><label>Date</label><input class="form-control" type="date" name="opening_date" value="{{ now()->toDateString() }}" required></div>
<div class="col-md-2"><label>Time</label><input class="form-control" type="time" name="opening_time" value="{{ now()->format('H:i') }}"></div>
<div class="col-md-2"><label>Amount</label><input class="form-control" type="number" name="opening_cash_amount" step="0.01" min="0" required></div>
<div class="col-md-8 mt-2"><input class="form-control" name="remarks" placeholder="Remarks"></div>
<div class="col-md-2 mt-2"><button class="btn btn-primary btn-block">Submit</button></div>
</form></div></div>

<div class="card"><div class="card-body table-responsive p-0"><table class="table table-sm mb-0"><thead><tr><th>No</th><th>Branch</th><th>Cashier</th><th>Date</th><th>Amount</th><th>Status</th><th></th></tr></thead><tbody>
@foreach($openings as $opening)
<tr><td>{{ $opening->opening_number }}</td><td>{{ $opening->branch?->name }}</td><td>{{ $opening->cashier?->display_name }}</td><td>{{ optional($opening->opening_date)->format('Y-m-d') }}</td><td>{{ number_format($opening->opening_cash_amount,2) }}</td><td><span class="badge badge-{{ $opening->status==='open'?'warning':'success' }}">{{ ucfirst($opening->status) }}</span></td><td>@if($opening->status==='open')<form method="POST" action="{{ route('finance.openings.close', $opening) }}">@csrf<button class="btn btn-xs btn-success">Close</button></form>@endif</td></tr>
@endforeach
</tbody></table></div><div class="card-footer">{{ $openings->links() }}</div></div>
@endsection
