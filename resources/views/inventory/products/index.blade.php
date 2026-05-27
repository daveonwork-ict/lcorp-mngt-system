@extends('layouts.app')
@section('page_title', 'Product Master List')
@section('content')
<div class="card mb-3"><div class="card-body">
<form method="GET" class="form-row">
    <div class="col-md-3"><input class="form-control" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search"></div>
    <div class="col-md-3"><select class="form-control" name="category_id"><option value="">Category</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(($filters['category_id'] ?? null)==$category->id)>{{ $category->category_name }}</option>@endforeach</select></div>
    <div class="col-md-3"><select class="form-control" name="brand_id"><option value="">Brand</option>@foreach($brands as $brand)<option value="{{ $brand->id }}" @selected(($filters['brand_id'] ?? null)==$brand->id)>{{ $brand->brand_name }}</option>@endforeach</select></div>
    <div class="col-md-2"><select class="form-control" name="status"><option value="">Status</option><option value="active" @selected(($filters['status'] ?? null)==='active')>Active</option><option value="inactive" @selected(($filters['status'] ?? null)==='inactive')>Inactive</option></select></div>
    <div class="col-md-1"><button class="btn btn-outline-primary btn-block">Go</button></div>
</form></div></div>
<div class="card"><div class="card-header text-right"><a class="btn btn-primary" href="{{ route('inventory.products.create') }}">Create Product</a></div><div class="card-body table-responsive p-0">
<table class="table table-hover"><thead><tr><th>Code</th><th>Name</th><th>Category</th><th>Brand</th><th>Price</th><th>Status</th><th></th></tr></thead><tbody>
@foreach($products as $product)
<tr><td>{{ $product->product_code }}</td><td>{{ $product->product_name }}</td><td>{{ $product->category?->category_name }}</td><td>{{ $product->brand?->brand_name }}</td><td>{{ number_format($product->selling_price,2) }}</td><td>{{ ucfirst($product->status) }}</td><td><a class="btn btn-xs btn-outline-secondary" href="{{ route('inventory.products.show',$product) }}">View</a></td></tr>
@endforeach
</tbody></table></div><div class="card-footer">{{ $products->links() }}</div></div>
@endsection
