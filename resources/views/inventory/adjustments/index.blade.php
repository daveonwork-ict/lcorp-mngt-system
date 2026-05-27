@extends('layouts.app')
@section('page_title', 'Inventory Adjustments')
@section('content')
<div class="card"><div class="card-header text-right"><a class="btn btn-primary" href="{{ route('inventory.adjustments.create') }}">Create Adjustment</a></div><div class="card-body table-responsive p-0"><table class="table table-sm"><thead><tr><th>No</th><th>Branch</th><th>Reason</th><th>Status</th><th></th></tr></thead><tbody>@foreach($adjustments as $adjustment)<tr><td>{{ $adjustment->adjustment_number }}</td><td>{{ $adjustment->branch?->name }}</td><td>{{ $adjustment->reason }}</td><td>{{ ucfirst($adjustment->status) }}</td><td><a class="btn btn-xs btn-outline-secondary" href="{{ route('inventory.adjustments.show',$adjustment) }}">View</a></td></tr>@endforeach</tbody></table></div><div class="card-footer">{{ $adjustments->links() }}</div></div>
@endsection
