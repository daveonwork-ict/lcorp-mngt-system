@extends('layouts.app')

@section('page_title', 'Warranty Records')
@section('content')
<div class="card mb-3"><div class="card-header">Warranty Search</div><div class="card-body"><form method="GET" class="form-row">
<div class="col-md-2"><input class="form-control" name="warranty_number" placeholder="Warranty #" value="{{ $filters['warranty_number'] ?? '' }}"></div>
<div class="col-md-2"><input class="form-control" name="receipt_number" placeholder="Receipt #" value="{{ $filters['receipt_number'] ?? '' }}"></div>
<div class="col-md-2"><input class="form-control" name="customer" placeholder="Customer" value="{{ $filters['customer'] ?? '' }}"></div>
<div class="col-md-2"><input class="form-control" name="imei" placeholder="IMEI" value="{{ $filters['imei'] ?? '' }}"></div>
<div class="col-md-2"><input class="form-control" name="product" placeholder="Product" value="{{ $filters['product'] ?? '' }}"></div>
<div class="col-md-2"><button class="btn btn-outline-primary btn-block">Search</button></div>
</form></div></div>
<div class="card"><div class="card-body table-responsive p-0"><table class="table table-sm mb-0"><thead><tr><th>#</th><th>Customer</th><th>Product</th><th>Receipt</th><th>Branch</th><th>End Date</th><th>Status</th></tr></thead><tbody>@foreach($warranties as $warranty)<tr><td><a href="{{ route('warranty.records.show', $warranty) }}">{{ $warranty->warranty_number }}</a></td><td>{{ $warranty->customer?->full_name }}</td><td>{{ $warranty->product?->product_name }}</td><td>{{ $warranty->sale?->sales_number }}</td><td>{{ $warranty->branch?->name }}</td><td>{{ optional($warranty->warranty_end_date)->format('Y-m-d') }}</td><td>{{ ucfirst($warranty->warranty_status) }}</td></tr>@endforeach</tbody></table></div><div class="card-footer">{{ $warranties->links() }}</div></div>
@endsection
