@extends('layouts.app')

@section('page_title', 'Sales List')
@section('content')
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="form-row">
            <div class="col-md-2"><input class="form-control" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}"></div>
            <div class="col-md-2"><input class="form-control" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}"></div>
            <div class="col-md-2"><select class="form-control" name="branch_id"><option value="">Branch</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected(($filters['branch_id'] ?? null) == $branch->id)>{{ $branch->name }}</option>@endforeach</select></div>
            <div class="col-md-2"><select class="form-control" name="cashier_id"><option value="">Cashier</option>@foreach($cashiers as $cashier)<option value="{{ $cashier->id }}" @selected(($filters['cashier_id'] ?? null) == $cashier->id)>{{ $cashier->display_name }}</option>@endforeach</select></div>
            <div class="col-md-2"><select class="form-control" name="payment_status"><option value="">Payment</option><option value="paid">Paid</option><option value="partial">Partial</option><option value="unpaid">Unpaid</option><option value="refunded">Refunded</option><option value="cancelled">Cancelled</option></select></div>
            <div class="col-md-2"><select class="form-control" name="sales_status"><option value="">Status</option><option value="completed">Completed</option><option value="held">Held</option><option value="voided">Voided</option><option value="refunded">Refunded</option><option value="partially_refunded">Partially Refunded</option><option value="cancelled">Cancelled</option></select></div>
            <div class="col-md-12 mt-2 text-right"><button class="btn btn-outline-primary">Apply Filters</button></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body table-responsive p-0">
        <table class="table table-hover table-sm">
            <thead><tr><th>No</th><th>Date</th><th>Branch</th><th>Cashier</th><th>Total</th><th>Paid</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @foreach($sales as $sale)
                    <tr>
                        <td>{{ $sale->sales_number }}</td>
                        <td>{{ $sale->sales_date }} {{ $sale->sales_time }}</td>
                        <td>{{ $sale->branch?->name }}</td>
                        <td>{{ $sale->cashier?->display_name }}</td>
                        <td>{{ number_format($sale->total_amount, 2) }}</td>
                        <td>{{ number_format($sale->paid_amount, 2) }}</td>
                        <td><span class="badge badge-info">{{ str($sale->sales_status)->replace('_', ' ')->title() }}</span></td>
                        <td><a class="btn btn-xs btn-outline-secondary" href="{{ route('sales.show', $sale) }}">View</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $sales->links() }}</div>
</div>
@endsection
