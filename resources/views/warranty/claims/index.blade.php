@extends('layouts.app')

@section('page_title', 'Warranty Claims')
@section('content')
<div class="card mb-3"><div class="card-header">Create Claim</div><div class="card-body"><form method="POST" action="{{ route('warranty.claims.store') }}" class="form-row">@csrf
<div class="col-md-4"><label>Warranty</label><select class="form-control" name="warranty_id" required>@foreach($warranties as $warranty)<option value="{{ $warranty->id }}">{{ $warranty->warranty_number }} - {{ $warranty->customer?->full_name }} - {{ $warranty->product?->product_name }}</option>@endforeach</select></div>
<div class="col-md-2"><label>Date</label><input class="form-control" type="date" name="claim_date" value="{{ now()->toDateString() }}" required></div>
<div class="col-md-3"><label>Issue</label><input class="form-control" name="issue_description" required></div>
<div class="col-md-3"><label>Condition</label><input class="form-control" name="product_condition"></div>
<div class="col-md-2 mt-2"><button class="btn btn-primary btn-block">Submit</button></div>
</form></div></div>

<div class="card"><div class="card-body table-responsive p-0"><table class="table table-sm mb-0"><thead><tr><th>#</th><th>Warranty</th><th>Customer</th><th>Status</th><th>Actions</th></tr></thead><tbody>
@foreach($claims as $claim)
<tr>
<td>{{ $claim->claim_number }}</td>
<td>{{ $claim->warranty?->warranty_number }}</td>
<td>{{ $claim->customer?->full_name }}</td>
<td><span class="badge badge-{{ in_array($claim->claim_status,['approved','ready_for_release','released','replaced'])?'success':(in_array($claim->claim_status,['rejected','cancelled'])?'danger':'warning') }}">{{ ucfirst(str_replace('_',' ', $claim->claim_status)) }}</span></td>
<td>
<form method="POST" action="{{ route('warranty.claims.approve',$claim) }}" class="d-inline">@csrf<button class="btn btn-xs btn-success">Approve</button></form>
<form method="POST" action="{{ route('warranty.claims.reject',$claim) }}" class="d-inline">@csrf<input type="hidden" name="remarks" value="Rejected"><button class="btn btn-xs btn-danger">Reject</button></form>
<form method="POST" action="{{ route('warranty.claims.status.update',$claim) }}" class="d-inline">@csrf<input type="hidden" name="status" value="under_repair"><button class="btn btn-xs btn-secondary">Under Repair</button></form>
<form method="POST" action="{{ route('warranty.claims.status.update',$claim) }}" class="d-inline">@csrf<input type="hidden" name="status" value="ready_for_release"><button class="btn btn-xs btn-info">Ready</button></form>
<form method="POST" action="{{ route('warranty.claims.status.update',$claim) }}" class="d-inline">@csrf<input type="hidden" name="status" value="released"><button class="btn btn-xs btn-primary">Release</button></form>
<form method="POST" enctype="multipart/form-data" action="{{ route('warranty.claims.attachments.store',$claim) }}" class="d-inline">@csrf<input type="file" name="attachment" required><button class="btn btn-xs btn-outline-dark">Upload</button></form>
<form method="POST" action="{{ route('warranty.claims.repair.store', $claim) }}" class="d-inline">@csrf<input type="hidden" name="repair_status" value="under_repair"><input type="hidden" name="repair_details" value="Repair tracking entry"><button class="btn btn-xs btn-outline-primary">Track Repair</button></form>
</td>
</tr>
<tr><td colspan="5"><strong>Timeline:</strong> @foreach($claim->statusLogs as $log)<span class="mr-2">{{ $log->created_at->format('Y-m-d H:i') }} {{ ucfirst(str_replace('_',' ', $log->status)) }}</span>@endforeach</td></tr>
@endforeach
</tbody></table></div><div class="card-footer">{{ $claims->links() }}</div></div>
@endsection
