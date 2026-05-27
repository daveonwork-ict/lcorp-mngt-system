@extends('layouts.app')

@section('page_title', 'Fund Transfers')
@section('content')
<div class="card mb-3"><div class="card-header">Request Transfer</div><div class="card-body"><form method="POST" enctype="multipart/form-data" action="{{ route('finance.transfers.store') }}" class="form-row">@csrf
<div class="col-md-3"><label>Source Branch</label><select class="form-control" name="source_branch_id"><option value="">None</option>@foreach($branches as $branch)<option value="{{ $branch->id }}">{{ $branch->name }}</option>@endforeach</select></div>
<div class="col-md-3"><label>Destination Branch</label><select class="form-control" name="destination_branch_id"><option value="">None</option>@foreach($branches as $branch)<option value="{{ $branch->id }}">{{ $branch->name }}</option>@endforeach</select></div>
<div class="col-md-2"><label>Amount</label><input class="form-control" type="number" step="0.01" min="0.01" name="amount" required></div>
<div class="col-md-2"><label>Method</label><input class="form-control" name="transfer_method" required></div>
<div class="col-md-2"><label>Reference</label><input class="form-control" name="reference_number"></div>
<div class="col-md-3 mt-2"><label>Proof</label><input class="form-control" type="file" name="proof_file" accept=".jpg,.jpeg,.png,.pdf"></div>
<div class="col-md-7 mt-2"><label>Remarks</label><input class="form-control" name="remarks"></div>
<div class="col-md-2 mt-2"><label>&nbsp;</label><button class="btn btn-primary btn-block">Submit</button></div>
</form></div></div>

<div class="card"><div class="card-body table-responsive p-0"><table class="table table-sm mb-0"><thead><tr><th>No</th><th>Source</th><th>Destination</th><th>Amount</th><th>Status</th><th></th></tr></thead><tbody>@foreach($transfers as $transfer)<tr><td>{{ $transfer->transfer_number }}</td><td>{{ $transfer->sourceBranch?->name ?? '-' }}</td><td>{{ $transfer->destinationBranch?->name ?? '-' }}</td><td>{{ number_format($transfer->amount,2) }}</td><td>{{ ucfirst($transfer->status) }}</td><td>@if($transfer->status==='pending')<form method="POST" action="{{ route('finance.transfers.approve',$transfer) }}" class="d-inline">@csrf<button class="btn btn-xs btn-success">Approve</button></form><form method="POST" action="{{ route('finance.transfers.reject',$transfer) }}" class="d-inline">@csrf<input type="hidden" name="rejection_reason" value="Rejected"><button class="btn btn-xs btn-danger">Reject</button></form>@endif</td></tr>@endforeach</tbody></table></div><div class="card-footer">{{ $transfers->links() }}</div></div>
@endsection
