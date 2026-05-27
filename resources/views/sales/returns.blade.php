@extends('layouts.app')

@section('page_title', 'Sales Returns / Exchanges')
@section('content')
<div class="row">
    <div class="col-md-5">
        <div class="card mb-3">
            <div class="card-header">Create Return / Exchange</div>
            <div class="card-body">
                <form method="POST" action="{{ route('sales.returns.store') }}">
                    @csrf
                    <div class="form-group"><label>Sale</label><select class="form-control" name="sale_id">@foreach($sales as $sale)<option value="{{ $sale->id }}">{{ $sale->sales_number }} - {{ number_format($sale->total_amount,2) }}</option>@endforeach</select></div>
                    <div class="form-group"><label>Type</label><select class="form-control" name="return_type"><option value="return">Return</option><option value="exchange">Exchange</option></select></div>
                    <div class="form-group"><label>Sale Item ID</label><input class="form-control" type="number" name="items[0][sale_item_id]" required></div>
                    <div class="form-group"><label>Quantity</label><input class="form-control" type="number" name="items[0][quantity]" min="1" required></div>
                    <div class="form-group"><label>Condition</label><input class="form-control" name="items[0][item_condition]"></div>
                    <div class="form-group"><label>Reason</label><textarea class="form-control" name="reason" rows="2"></textarea></div>
                    <button class="btn btn-primary btn-block">Submit Request</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card">
            <div class="card-header">Return Requests</div>
            <div class="card-body table-responsive p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>No</th><th>Sale</th><th>Type</th><th>Refund</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @foreach($returns as $return)
                            <tr>
                                <td>{{ $return->return_number }}</td>
                                <td>{{ $return->sale?->sales_number }}</td>
                                <td>{{ ucfirst($return->return_type) }}</td>
                                <td>{{ number_format($return->refund_amount, 2) }}</td>
                                <td>{{ ucfirst($return->status) }}</td>
                                <td>
                                    @if($return->status === 'pending')
                                        <form method="POST" action="{{ route('sales.returns.approve', $return) }}" class="d-inline">@csrf<button class="btn btn-xs btn-success">Approve</button></form>
                                        <form method="POST" action="{{ route('sales.returns.reject', $return) }}" class="d-inline">@csrf<button class="btn btn-xs btn-danger">Reject</button></form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer">{{ $returns->links() }}</div>
        </div>
    </div>
</div>
@endsection
