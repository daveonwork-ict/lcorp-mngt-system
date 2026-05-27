@extends('layouts.app')
@section('page_title', 'Create Stock-In')
@section('content')
<form method="POST" action="{{ route('inventory.stock-ins.store') }}">
@csrf
<div class="card"><div class="card-body"><div class="form-row">
<div class="col-md-3"><label>Stock-In No</label><input class="form-control" name="stock_in_number" value="{{ old('stock_in_number') }}" required></div>
<div class="col-md-3"><label>Branch</label><select class="form-control" name="branch_id">@foreach($branches as $branch)<option value="{{ $branch->id }}">{{ $branch->name }}</option>@endforeach</select></div>
<div class="col-md-3"><label>Received Date</label><input class="form-control" type="date" name="received_date" value="{{ old('received_date', now()->toDateString()) }}"></div>
<div class="col-md-3"><label>Status</label><select class="form-control" name="status"><option value="pending">Pending</option><option value="approved">Approved</option></select></div>
</div>
<hr>
<div id="itemRows">
    <div class="form-row mb-2">
        <div class="col-md-5"><select class="form-control" name="items[0][product_id]">@foreach($products as $product)<option value="{{ $product->id }}">{{ $product->product_name }}</option>@endforeach</select></div>
        <div class="col-md-2"><input class="form-control" type="number" name="items[0][quantity]" value="1" min="1"></div>
        <div class="col-md-2"><input class="form-control" type="number" step="0.01" name="items[0][cost_price]" placeholder="Cost"></div>
        <div class="col-md-2"><input class="form-control" type="number" step="0.01" name="items[0][selling_price]" placeholder="Selling"></div>
    </div>
</div>
<p class="text-muted mb-0">Use one row for now. Additional rows can be added after initial submission if needed.</p>
</div><div class="card-footer text-right"><button class="btn btn-primary">Save Stock-In</button></div></div>
</form>
@endsection
