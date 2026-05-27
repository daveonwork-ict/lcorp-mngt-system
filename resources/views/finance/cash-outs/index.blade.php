@extends('layouts.app')

@section('page_title', 'Cash Out')
@section('content')
<div class="card mb-3"><div class="card-header">Record Manual Cash Out</div><div class="card-body"><form method="POST" action="{{ route('finance.cash-outs.store') }}" class="form-row">@csrf
<div class="col-md-3"><label>Branch</label><select class="form-control" name="branch_id">@foreach($branches as $branch)<option value="{{ $branch->id }}">{{ $branch->name }}</option>@endforeach</select></div>
<div class="col-md-3"><label>Source</label><input class="form-control" name="source_type" placeholder="utilities / repairs" required></div>
<div class="col-md-2"><label>Amount</label><input class="form-control" type="number" step="0.01" min="0.01" name="amount" required></div>
<div class="col-md-2"><label>Payment</label><select class="form-control" name="payment_method_id"><option value="">N/A</option>@foreach($paymentMethods as $method)<option value="{{ $method->id }}">{{ $method->payment_method_name }}</option>@endforeach</select></div>
<div class="col-md-2"><label>Remarks</label><input class="form-control" name="remarks"></div>
<div class="col-md-2 mt-2"><button class="btn btn-primary btn-block">Record</button></div>
</form></div></div>

<div class="card"><div class="card-body table-responsive p-0"><table class="table table-sm mb-0"><thead><tr><th>No</th><th>Branch</th><th>Source</th><th>Amount</th><th>Released</th></tr></thead><tbody>@foreach($cashOuts as $entry)<tr><td>{{ $entry->cash_out_number }}</td><td>{{ $entry->branch?->name }}</td><td>{{ $entry->source_type }}</td><td>{{ number_format($entry->amount,2) }}</td><td>{{ $entry->released_at }}</td></tr>@endforeach</tbody></table></div><div class="card-footer">{{ $cashOuts->links() }}</div></div>
@endsection
