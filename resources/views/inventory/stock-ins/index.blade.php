@extends('layouts.app')
@section('page_title', 'Stock-In')
@section('content')
<div class="card"><div class="card-header text-right"><a class="btn btn-primary" href="{{ route('inventory.stock-ins.create') }}">New Stock-In</a></div><div class="card-body table-responsive p-0"><table class="table table-sm"><thead><tr><th>No</th><th>Branch</th><th>Date</th><th>Status</th><th></th></tr></thead><tbody>@foreach($stockIns as $stockIn)<tr><td>{{ $stockIn->stock_in_number }}</td><td>{{ $stockIn->branch?->name }}</td><td>{{ $stockIn->received_date }}</td><td>{{ ucfirst($stockIn->status) }}</td><td><a class="btn btn-xs btn-outline-secondary" href="{{ route('inventory.stock-ins.show',$stockIn) }}">View</a></td></tr>@endforeach</tbody></table></div><div class="card-footer">{{ $stockIns->links() }}</div></div>
@endsection
