@extends('layouts.app')
@section('page_title', 'Transfer Details')
@section('content')
<div class="card"><div class="card-header d-flex justify-content-between"><span>{{ $transfer->transfer_number }}</span><div>
@if(in_array($transfer->status,['pending_approval','approved']))<form class="d-inline" method="POST" action="{{ route('inventory.transfers.approve',$transfer) }}">@csrf<button class="btn btn-sm btn-success">Approve</button></form>@endif
@if($transfer->status==='in_transit')<form class="d-inline" method="POST" action="{{ route('inventory.transfers.receive',$transfer) }}">@csrf<button class="btn btn-sm btn-primary">Receive</button></form>@endif
</div></div>
<div class="card-body"><p><strong>From:</strong> {{ $transfer->sourceBranch?->name }} | <strong>To:</strong> {{ $transfer->destinationBranch?->name }}</p>
<p><strong>Status:</strong> {{ ucfirst(str_replace('_',' ',$transfer->status)) }}</p>
<table class="table table-sm"><thead><tr><th>Product</th><th>Qty</th><th>IMEI</th></tr></thead><tbody>@foreach($transfer->items as $item)<tr><td>{{ $item->product?->product_name }}</td><td>{{ $item->quantity }}</td><td>{{ $item->productImei?->imei_number }}</td></tr>@endforeach</tbody></table></div></div>
@endsection
