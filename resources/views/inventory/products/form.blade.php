@extends('layouts.app')
@section('page_title', $mode === 'create' ? 'Create Product' : 'Edit Product')
@section('content')
<form method="POST" action="{{ $mode==='create' ? route('inventory.products.store') : route('inventory.products.update',$product) }}">
@csrf @if($mode==='edit') @method('PUT') @endif
<div class="card"><div class="card-body">
<div class="form-row">
<div class="col-md-3"><label>Product Code</label><input class="form-control" name="product_code" value="{{ old('product_code',$product->product_code) }}" required></div>
<div class="col-md-3"><label>SKU</label><input class="form-control" name="sku" value="{{ old('sku',$product->sku) }}" required></div>
<div class="col-md-3"><label>Barcode</label><input class="form-control" name="barcode" value="{{ old('barcode',$product->barcode) }}"></div>
<div class="col-md-3"><label>Name</label><input class="form-control" name="product_name" value="{{ old('product_name',$product->product_name) }}" required></div>
<div class="col-md-3 mt-3"><label>Category</label><select class="form-control" name="category_id" required>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id',$product->category_id)==$category->id)>{{ $category->category_name }}</option>@endforeach</select></div>
<div class="col-md-3 mt-3"><label>Brand</label><select class="form-control" name="brand_id" required>@foreach($brands as $brand)<option value="{{ $brand->id }}" @selected(old('brand_id',$product->brand_id)==$brand->id)>{{ $brand->brand_name }}</option>@endforeach</select></div>
<div class="col-md-2 mt-3"><label>Cost</label><input class="form-control" type="number" step="0.01" name="cost_price" value="{{ old('cost_price',$product->cost_price) }}" required></div>
<div class="col-md-2 mt-3"><label>Selling</label><input class="form-control" type="number" step="0.01" name="selling_price" value="{{ old('selling_price',$product->selling_price) }}" required></div>
<div class="col-md-2 mt-3"><label>Wholesale</label><input class="form-control" type="number" step="0.01" name="wholesale_price" value="{{ old('wholesale_price',$product->wholesale_price) }}"></div>
<div class="col-md-2 mt-3"><label>Reorder Level</label><input class="form-control" type="number" name="reorder_level" value="{{ old('reorder_level',$product->reorder_level ?? 0) }}" required></div>
<div class="col-md-2 mt-3"><label>Warranty Duration</label><input class="form-control" type="number" name="warranty_duration" value="{{ old('warranty_duration',$product->warranty_duration ?? 0) }}" required></div>
<div class="col-md-2 mt-3"><label>Duration Type</label><select class="form-control" name="warranty_duration_type"><option value="day" @selected(old('warranty_duration_type',$product->warranty_duration_type ?? 'month')==='day')>Day</option><option value="week" @selected(old('warranty_duration_type',$product->warranty_duration_type ?? 'month')==='week')>Week</option><option value="month" @selected(old('warranty_duration_type',$product->warranty_duration_type ?? 'month')==='month')>Month</option><option value="year" @selected(old('warranty_duration_type',$product->warranty_duration_type ?? 'month')==='year')>Year</option></select></div>
<div class="col-md-2 mt-3"><label>Status</label><select class="form-control" name="status"><option value="active" @selected(old('status',$product->status ?? 'active')==='active')>Active</option><option value="inactive" @selected(old('status',$product->status)==='inactive')>Inactive</option></select></div>
<div class="col-md-2 mt-3"><label>IMEI Required</label><div><input type="checkbox" name="is_imei_required" value="1" @checked(old('is_imei_required',$product->is_imei_required))> Yes</div></div>
<div class="col-md-2 mt-3"><label>Serialized</label><div><input type="checkbox" name="is_serialized" value="1" @checked(old('is_serialized',$product->is_serialized))> Yes</div></div>
<div class="col-md-12 mt-3"><label>Description</label><textarea class="form-control" name="description">{{ old('description',$product->description) }}</textarea></div>
</div>
</div><div class="card-footer text-right"><button class="btn btn-primary">Save Product</button></div></div>
</form>
@endsection
