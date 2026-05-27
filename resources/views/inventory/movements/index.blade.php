@extends('layouts.app')
@section('page_title', 'Inventory Ledger')
@section('content')
<div class="card"><div class="card-body table-responsive p-0"><table class="table table-sm"><thead><tr><th>Date</th><th>Branch</th><th>Product</th><th>Type</th><th>In</th><th>Out</th><th>Balance</th><th>Ref</th></tr></thead><tbody>@foreach($movements as $movement)<tr><td>{{ $movement->created_at }}</td><td>{{ $movement->branch?->name }}</td><td>{{ $movement->product?->product_name }}</td><td>{{ $movement->movement_type }}</td><td>{{ $movement->quantity_in }}</td><td>{{ $movement->quantity_out }}</td><td>{{ $movement->balance_after }}</td><td>{{ $movement->reference_type }} #{{ $movement->reference_id }}</td></tr>@endforeach</tbody></table></div><div class="card-footer">{{ $movements->links() }}</div></div>
@endsection
