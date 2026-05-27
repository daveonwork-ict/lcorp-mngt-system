@extends('layouts.app')
@section('page_title', 'Product Details')
@section('content')
<div class="card mb-3"><div class="card-header d-flex justify-content-between"><strong>{{ $product->product_name }}</strong><a class="btn btn-sm btn-outline-primary" href="{{ route('inventory.products.edit',$product) }}">Edit</a></div>
<div class="card-body"><div class="row">
<div class="col-md-4"><p><strong>Code:</strong> {{ $product->product_code }}</p><p><strong>SKU:</strong> {{ $product->sku }}</p><p><strong>Barcode:</strong> {{ $product->barcode }}</p></div>
<div class="col-md-4"><p><strong>Category:</strong> {{ $product->category?->category_name }}</p><p><strong>Brand:</strong> {{ $product->brand?->brand_name }}</p><p><strong>Status:</strong> {{ ucfirst($product->status) }}</p></div>
<div class="col-md-4"><p><strong>Cost:</strong> {{ number_format($product->cost_price,2) }}</p><p><strong>Selling:</strong> {{ number_format($product->selling_price,2) }}</p><p><strong>Reorder Level:</strong> {{ $product->reorder_level }}</p></div>
</div></div></div>
<div class="row">
<div class="col-md-6"><div class="card"><div class="card-header">Branch Inventory</div><div class="card-body table-responsive p-0"><table class="table table-sm"><thead><tr><th>Branch</th><th>On Hand</th><th>Available</th><th>Value</th></tr></thead><tbody>@foreach($product->inventories as $inventory)<tr><td>{{ $inventory->branch?->name }}</td><td>{{ $inventory->quantity_on_hand }}</td><td>{{ $inventory->quantity_available }}</td><td>{{ number_format($inventory->inventory_value,2) }}</td></tr>@endforeach</tbody></table></div></div></div>
<div class="col-md-6"><div class="card"><div class="card-header">IMEI Records</div><div class="card-body table-responsive p-0"><table class="table table-sm"><thead><tr><th>IMEI</th><th>Status</th><th>Branch</th></tr></thead><tbody>@foreach($product->imeis as $imei)<tr><td>{{ $imei->imei_number }}</td><td>{{ ucfirst($imei->status) }}</td><td>{{ $imei->branch?->name }}</td></tr>@endforeach</tbody></table></div></div></div>
</div>
@endsection
