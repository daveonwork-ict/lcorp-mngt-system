@extends('layouts.app')
@section('page_title', 'Physical Count')
@section('content')
<div class="card"><div class="card-header text-right"><a class="btn btn-primary" href="{{ route('inventory.physical-counts.create') }}">Create Count</a></div><div class="card-body table-responsive p-0"><table class="table table-sm"><thead><tr><th>No</th><th>Branch</th><th>Status</th><th></th></tr></thead><tbody>@foreach($counts as $count)<tr><td>{{ $count->count_number }}</td><td>{{ $count->branch?->name }}</td><td>{{ ucfirst($count->status) }}</td><td><a class="btn btn-xs btn-outline-secondary" href="{{ route('inventory.physical-counts.show',$count) }}">View</a></td></tr>@endforeach</tbody></table></div><div class="card-footer">{{ $counts->links() }}</div></div>
@endsection
