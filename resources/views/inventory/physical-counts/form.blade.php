@extends('layouts.app')
@section('page_title', 'Create Physical Count')
@section('content')
<form method="POST" action="{{ route('inventory.physical-counts.store') }}">@csrf
<div class="card"><div class="card-body"><div class="form-row">
<div class="col-md-3"><label>Count No</label><input class="form-control" name="count_number" required></div>
<div class="col-md-3"><label>Branch</label><select class="form-control" name="branch_id">@foreach($branches as $branch)<option value="{{ $branch->id }}">{{ $branch->name }}</option>@endforeach</select></div>
<div class="col-md-3"><label>Category</label><select class="form-control" name="category_id"><option value="">All</option>@foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->category_name }}</option>@endforeach</select></div>
<div class="col-md-3"><label>Status</label><select class="form-control" name="status"><option value="open">Open</option></select></div>
</div><hr><div class="form-row"><div class="col-md-5"><select class="form-control" name="items[0][product_id]">@foreach($products as $product)<option value="{{ $product->id }}">{{ $product->product_name }}</option>@endforeach</select></div><div class="col-md-3"><input class="form-control" type="number" name="items[0][counted_quantity]" value="0" min="0"></div><div class="col-md-2"><input class="form-control" name="items[0][encoded_imei]" placeholder="IMEI"></div><div class="col-md-2"><input class="form-control" name="items[0][remarks]" placeholder="Remarks"></div></div></div><div class="card-footer text-right"><button class="btn btn-primary">Save</button></div></div>
</form>
@endsection
