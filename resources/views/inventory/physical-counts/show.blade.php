@extends('layouts.app')
@section('page_title', 'Physical Count Details')
@section('content')
<div class="card"><div class="card-header d-flex justify-content-between"><span>{{ $count->count_number }}</span><div>
@if($count->status==='open')<form class="d-inline" method="POST" action="{{ route('inventory.physical-counts.submit',$count) }}">@csrf<button class="btn btn-sm btn-warning">Submit</button></form>@endif
@if(in_array($count->status,['submitted','reviewed']))<form class="d-inline" method="POST" action="{{ route('inventory.physical-counts.generate-adjustment',$count) }}">@csrf<button class="btn btn-sm btn-primary">Generate Adjustment</button></form>@endif
</div></div>
<div class="card-body"><p><strong>Branch:</strong> {{ $count->branch?->name }}</p><p><strong>Status:</strong> {{ ucfirst($count->status) }}</p>
<table class="table table-sm"><thead><tr><th>Product</th><th>System</th><th>Counted</th><th>Variance</th></tr></thead><tbody>@foreach($count->items as $item)<tr><td>{{ $item->product?->product_name }}</td><td>{{ $item->system_quantity }}</td><td>{{ $item->counted_quantity }}</td><td>{{ $item->variance }}</td></tr>@endforeach</tbody></table></div></div>
@endsection
