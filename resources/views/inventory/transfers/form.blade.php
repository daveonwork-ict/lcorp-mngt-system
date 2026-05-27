@extends('layouts.app')
@section('page_title', 'Create Inventory Transfer')
@section('content')
<form method="POST" action="{{ route('inventory.transfers.store') }}">@csrf
<div class="card"><div class="card-body"><div class="form-row">
<div class="col-md-3"><label>Transfer No</label><input class="form-control" name="transfer_number" required></div>
<div class="col-md-3"><label>Source</label><select class="form-control" name="source_branch_id">@foreach($branches as $branch)<option value="{{ $branch->id }}">{{ $branch->name }}</option>@endforeach</select></div>
<div class="col-md-3"><label>Destination</label><select class="form-control" name="destination_branch_id">@foreach($branches as $branch)<option value="{{ $branch->id }}">{{ $branch->name }}</option>@endforeach</select></div>
<div class="col-md-3"><label>Status</label><select class="form-control" name="status"><option value="pending_approval">Pending Approval</option></select></div>
</div><hr><div class="form-row"><div class="col-md-4"><select class="form-control" name="items[0][product_id]">@foreach($products as $product)<option value="{{ $product->id }}">{{ $product->product_name }}</option>@endforeach</select></div><div class="col-md-2"><input class="form-control" type="number" name="items[0][quantity]" value="1" min="1"></div><div class="col-md-4"><select class="form-control" name="items[0][imei_id]"><option value="">No IMEI</option>@foreach($imeis as $imei)<option value="{{ $imei->id }}">{{ $imei->imei_number }}</option>@endforeach</select></div><div class="col-md-2"><input class="form-control" name="items[0][remarks]" placeholder="Remarks"></div></div></div><div class="card-footer text-right"><button class="btn btn-primary">Save</button></div></div>
</form>
@endsection
