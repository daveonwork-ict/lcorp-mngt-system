@extends('layouts.app')

@section('page_title', 'Sales Reports')
@section('content')
<div class="card mb-3"><div class="card-body"><form method="GET" class="form-row">
<div class="col-md-2 mb-2"><input type="date" class="form-control" name="date_from" value="{{ $filters['date_from'] ?? '' }}"></div>
<div class="col-md-2 mb-2"><input type="date" class="form-control" name="date_to" value="{{ $filters['date_to'] ?? '' }}"></div>
<div class="col-md-2 mb-2"><select class="form-control" name="branch_id"><option value="">Branch</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected(($filters['branch_id'] ?? null)==$branch->id)>{{ $branch->branch_name ?? $branch->name }}</option>@endforeach</select></div>
<div class="col-md-2 mb-2"><select class="form-control" name="cashier_id"><option value="">Cashier</option>@foreach($cashiers as $cashier)<option value="{{ $cashier->id }}" @selected(($filters['cashier_id'] ?? null)==$cashier->id)>{{ $cashier->display_name }}</option>@endforeach</select></div>
<div class="col-md-4 mb-2 text-right"><button class="btn btn-outline-primary">Apply</button> <a class="btn btn-outline-success" href="{{ route('reports.sales.export-csv', request()->query()) }}">Export CSV</a> <a class="btn btn-outline-success" href="{{ route('reports.sales.export-excel', request()->query()) }}">Export Excel</a> <a class="btn btn-outline-secondary" target="_blank" href="{{ route('reports.sales.print', request()->query()) }}">Print</a></div>
</form></div></div>

<div class="card"><div class="card-body table-responsive p-0"><table class="table table-sm mb-0"><thead><tr><th>#</th><th>Date</th><th>Branch</th><th>Cashier</th><th>Customer</th><th>Total</th><th>Status</th></tr></thead><tbody>@foreach($sales as $sale)<tr><td>{{ $sale->sales_number }}</td><td>{{ optional($sale->sales_date)->format('Y-m-d') }}</td><td>{{ $sale->branch?->branch_name ?? $sale->branch?->name }}</td><td>{{ $sale->cashier?->display_name }}</td><td>{{ $sale->customer?->full_name }}</td><td>{{ number_format((float) $sale->total_amount,2) }}</td><td>{{ ucfirst($sale->sales_status) }}</td></tr>@endforeach</tbody></table></div><div class="card-footer">{{ $sales->links() }}</div></div>
@endsection
