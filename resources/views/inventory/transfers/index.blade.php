@extends('layouts.app')
@section('page_title', 'Inventory Transfers')
@section('content')
<div class="card"><div class="card-header text-right"><a class="btn btn-primary" href="{{ route('inventory.transfers.create') }}">Create Transfer</a></div><div class="card-body table-responsive p-0"><table class="table table-sm"><thead><tr><th>No</th><th>Source</th><th>Destination</th><th>Status</th><th></th></tr></thead><tbody>@foreach($transfers as $transfer)<tr><td>{{ $transfer->transfer_number }}</td><td>{{ $transfer->sourceBranch?->name }}</td><td>{{ $transfer->destinationBranch?->name }}</td><td>{{ ucfirst(str_replace('_',' ',$transfer->status)) }}</td><td><a class="btn btn-xs btn-outline-secondary" href="{{ route('inventory.transfers.show',$transfer) }}">View</a></td></tr>@endforeach</tbody></table></div><div class="card-footer">{{ $transfers->links() }}</div></div>
@endsection
