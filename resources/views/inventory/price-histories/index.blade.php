@extends('layouts.app')
@section('page_title', 'Price History')
@section('content')
<div class="card mb-3"><div class="card-body"><form class="form-inline" method="GET"><label class="mr-2">Product</label><select class="form-control mr-2" name="product_id"><option value="">All</option>@foreach($products as $product)<option value="{{ $product->id }}" @selected(($filters['product_id'] ?? null)==$product->id)>{{ $product->product_name }}</option>@endforeach</select><button class="btn btn-outline-primary">Filter</button></form></div></div>
<div class="card"><div class="card-body table-responsive p-0"><table class="table table-sm"><thead><tr><th>Date</th><th>Product</th><th>Old Cost</th><th>New Cost</th><th>Old Sell</th><th>New Sell</th></tr></thead><tbody>@foreach($histories as $history)<tr><td>{{ $history->changed_at }}</td><td>{{ $history->product?->product_name }}</td><td>{{ $history->old_cost_price }}</td><td>{{ $history->new_cost_price }}</td><td>{{ $history->old_selling_price }}</td><td>{{ $history->new_selling_price }}</td></tr>@endforeach</tbody></table></div><div class="card-footer">{{ $histories->links() }}</div></div>
@endsection
