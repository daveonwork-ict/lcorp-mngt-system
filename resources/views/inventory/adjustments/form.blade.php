@extends('layouts.app')
@section('page_title', 'Create Adjustment')
@section('content')
<form method="POST" action="{{ route('inventory.adjustments.store') }}">@csrf
<div class="card"><div class="card-body"><div class="form-row">
<div class="col-md-3"><label>Adjustment No</label><input class="form-control" name="adjustment_number" required></div>
<div class="col-md-3"><label>Branch</label><select class="form-control" name="branch_id">@foreach($branches as $branch)<option value="{{ $branch->id }}">{{ $branch->name }}</option>@endforeach</select></div>
<div class="col-md-3"><label>Status</label><select class="form-control" name="status"><option value="pending">Pending</option></select></div>
<div class="col-md-3"><label>Reason</label><input class="form-control" name="reason" required></div>
</div><hr><div class="form-row"><div class="col-md-6"><select class="form-control" name="items[0][product_id]">@foreach($products as $product)<option value="{{ $product->id }}">{{ $product->product_name }}</option>@endforeach</select></div><div class="col-md-3"><input class="form-control" type="number" name="items[0][quantity_after]" min="0" value="0"></div><div class="col-md-3"><input class="form-control" name="items[0][remarks]" placeholder="Remarks"></div></div></div><div class="card-footer text-right"><button class="btn btn-primary">Save</button></div></div>
</form>
@endsection
