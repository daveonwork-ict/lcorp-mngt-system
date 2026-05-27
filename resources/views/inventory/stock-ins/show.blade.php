@extends('layouts.app')
@section('page_title', 'Stock-In Details')
@section('content')
<div class="card"><div class="card-header d-flex justify-content-between"><span>{{ $stockIn->stock_in_number }}</span>@if($stockIn->status!=='approved')<form method="POST" action="{{ route('inventory.stock-ins.approve',$stockIn) }}">@csrf<button class="btn btn-sm btn-success">Approve</button></form>@endif</div>
<div class="card-body"><p><strong>Branch:</strong> {{ $stockIn->branch?->name }}</p><p><strong>Status:</strong> {{ ucfirst($stockIn->status) }}</p>
<table class="table table-sm"><thead><tr><th>Product</th><th>Qty</th><th>Cost</th><th>Selling</th></tr></thead><tbody>@foreach($stockIn->items as $item)<tr><td>{{ $item->product?->product_name }}</td><td>{{ $item->quantity }}</td><td>{{ $item->cost_price }}</td><td>{{ $item->selling_price }}</td></tr>@endforeach</tbody></table>
</div></div>
@endsection
