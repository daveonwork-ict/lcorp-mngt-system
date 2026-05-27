@extends('layouts.app')
@section('page_title', 'Adjustment Details')
@section('content')
<div class="card"><div class="card-header d-flex justify-content-between"><span>{{ $adjustment->adjustment_number }}</span>@if($adjustment->status!=='approved')<form method="POST" action="{{ route('inventory.adjustments.approve',$adjustment) }}">@csrf<button class="btn btn-sm btn-success">Approve</button></form>@endif</div>
<div class="card-body"><p><strong>Branch:</strong> {{ $adjustment->branch?->name }}</p><p><strong>Reason:</strong> {{ $adjustment->reason }}</p><table class="table table-sm"><thead><tr><th>Product</th><th>Before</th><th>After</th><th>Variance</th></tr></thead><tbody>@foreach($adjustment->items as $item)<tr><td>{{ $item->product?->product_name }}</td><td>{{ $item->quantity_before }}</td><td>{{ $item->quantity_after }}</td><td>{{ $item->variance }}</td></tr>@endforeach</tbody></table></div></div>
@endsection
