@extends('layouts.app')

@section('page_title', 'Daily Closing')
@section('content')
<div class="card mb-3"><div class="card-header">Submit Daily Closing</div><div class="card-body"><form method="POST" action="{{ route('finance.closings.store') }}" class="form-row">@csrf
<div class="col-md-2"><label>Branch</label><select class="form-control" name="branch_id">@foreach($branches as $branch)<option value="{{ $branch->id }}">{{ $branch->name }}</option>@endforeach</select></div>
<div class="col-md-2"><label>Cashier</label><select class="form-control" name="cashier_id">@foreach($cashiers as $cashier)<option value="{{ $cashier->id }}">{{ $cashier->display_name }}</option>@endforeach</select></div>
<div class="col-md-2"><label>Date</label><input class="form-control" type="date" name="closing_date" value="{{ now()->toDateString() }}"></div>
<div class="col-md-2"><label>Actual Cash</label><input class="form-control" type="number" step="0.01" min="0" name="actual_cash" required></div>
<div class="col-md-4"><label>Variance Explanation</label><input class="form-control" name="variance_explanation"></div>
<div class="col-md-12 mt-2"><label>Denominations</label><div class="row">@foreach($denominations as $den)<div class="col-6 col-md-2 mb-2"><div class="input-group input-group-sm"><div class="input-group-prepend"><span class="input-group-text">{{ $den }}</span></div><input class="form-control" type="number" name="denominations[{{ $loop->index }}][quantity]" value="0" min="0"><input type="hidden" name="denominations[{{ $loop->index }}][denomination]" value="{{ $den }}"></div></div>@endforeach</div></div>
<div class="col-md-12"><label>Remarks</label><input class="form-control" name="remarks"></div>
<div class="col-md-2 mt-2"><button class="btn btn-primary btn-block">Submit</button></div>
</form></div></div>

<div class="card"><div class="card-body table-responsive p-0"><table class="table table-sm mb-0"><thead><tr><th>No</th><th>Branch</th><th>Date</th><th>Expected</th><th>Actual</th><th>Variance</th><th>Status</th><th></th></tr></thead><tbody>@foreach($closings as $closing)<tr><td>{{ $closing->closing_number }}</td><td>{{ $closing->branch?->name }}</td><td>{{ optional($closing->closing_date)->format('Y-m-d') }}</td><td>{{ number_format($closing->expected_cash,2) }}</td><td>{{ number_format($closing->actual_cash,2) }}</td><td><span class="badge badge-{{ $closing->variance_type==='balanced'?'success':'warning' }}">{{ ucfirst($closing->variance_type) }} ({{ number_format($closing->variance_amount,2) }})</span></td><td>{{ ucfirst($closing->status) }}</td><td><form method="POST" action="{{ route('finance.closings.review', $closing) }}" class="form-inline">@csrf<select class="form-control form-control-sm mr-1" name="status"><option value="reviewed">Reviewed</option><option value="approved">Approved</option><option value="rejected">Rejected</option></select><button class="btn btn-xs btn-primary">Save</button></form></td></tr>@endforeach</tbody></table></div><div class="card-footer">{{ $closings->links() }}</div></div>
@endsection
