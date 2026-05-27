@extends('layouts.app')

@section('page_title', 'Void Requests')
@section('content')
<div class="card">
    <div class="card-body table-responsive p-0">
        <table class="table table-sm mb-0">
            <thead><tr><th>Sale</th><th>Branch</th><th>Reason</th><th>Status</th><th>Requested By</th><th></th></tr></thead>
            <tbody>
                @foreach($voidRequests as $request)
                    <tr>
                        <td>{{ $request->sale?->sales_number }}</td>
                        <td>{{ $request->sale?->branch?->name }}</td>
                        <td>{{ $request->reason }}</td>
                        <td>{{ ucfirst($request->status) }}</td>
                        <td>{{ $request->requester?->display_name }}</td>
                        <td>
                            @if($request->status === 'pending')
                                <form method="POST" action="{{ route('sales.voids.approve', $request) }}" class="d-inline">@csrf<button class="btn btn-xs btn-success">Approve</button></form>
                                <form method="POST" action="{{ route('sales.voids.reject', $request) }}" class="d-inline">@csrf<button class="btn btn-xs btn-danger">Reject</button></form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $voidRequests->links() }}</div>
</div>
@endsection
