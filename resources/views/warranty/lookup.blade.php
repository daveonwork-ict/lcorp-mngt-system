@extends('layouts.app')

@section('page_title', 'Warranty Lookup')
@section('content')
<div class="card mb-3"><div class="card-body"><form method="GET" class="form-row">
<div class="col-md-2"><input class="form-control" name="warranty_number" placeholder="Warranty #" value="{{ $filters['warranty_number'] ?? '' }}"></div>
<div class="col-md-2"><input class="form-control" name="receipt_number" placeholder="Receipt #" value="{{ $filters['receipt_number'] ?? '' }}"></div>
<div class="col-md-2"><input class="form-control" name="customer" placeholder="Customer" value="{{ $filters['customer'] ?? '' }}"></div>
<div class="col-md-2"><input class="form-control" name="imei" placeholder="IMEI" value="{{ $filters['imei'] ?? '' }}"></div>
<div class="col-md-2"><input class="form-control" name="product" placeholder="Product" value="{{ $filters['product'] ?? '' }}"></div>
<div class="col-md-2"><select class="form-control" name="branch_id"><option value="">Branch</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected(($filters['branch_id'] ?? null)==$branch->id)>{{ $branch->name }}</option>@endforeach</select></div>
<div class="col-md-2 mt-2"><button class="btn btn-outline-primary btn-block">Lookup</button></div>
</form></div></div>
<div class="card"><div class="card-body table-responsive p-0"><table class="table table-sm mb-0"><thead><tr><th>#</th><th>Customer</th><th>Product</th><th>Expires</th><th>Status</th><th>Claims</th></tr></thead><tbody>@foreach($results as $item)<tr><td>{{ $item->warranty_number }}</td><td>{{ $item->customer?->full_name }}</td><td>{{ $item->product?->product_name }}</td><td>{{ optional($item->warranty_end_date)->format('Y-m-d') }}</td><td>{{ ucfirst($item->warranty_status) }}</td><td>{{ $item->claims->count() }}</td></tr>@endforeach</tbody></table></div><div class="card-footer">{{ $results->links() }}</div></div>
@endsection
