@extends('layouts.app')

@section('page_title', 'Warranty Dashboard')
@section('content')
<div class="row">
    @foreach(['active_warranties','expired_warranties','pending_claims','approved_claims','under_repair','ready_for_release','replaced_items','rejected_claims'] as $key)
        <div class="col-md-3 mb-3">
            <div class="card h-100"><div class="card-body"><div class="text-muted small text-uppercase">{{ str_replace('_',' ', $key) }}</div><div class="h4 mb-0">{{ $dashboard[$key] }}</div></div></div>
        </div>
    @endforeach
</div>
<div class="row">
    <div class="col-lg-6 mb-3"><div class="card"><div class="card-header">Recent Claims</div><div class="card-body table-responsive p-0"><table class="table table-sm mb-0"><thead><tr><th>#</th><th>Customer</th><th>Product</th><th>Status</th></tr></thead><tbody>@foreach($dashboard['recent_claims'] as $claim)<tr><td>{{ $claim->claim_number }}</td><td>{{ $claim->customer?->full_name }}</td><td>{{ $claim->warranty?->product?->product_name }}</td><td>{{ ucfirst(str_replace('_',' ',$claim->claim_status)) }}</td></tr>@endforeach</tbody></table></div></div></div>
    <div class="col-lg-6 mb-3"><div class="card"><div class="card-header">Expiring Warranties</div><div class="card-body table-responsive p-0"><table class="table table-sm mb-0"><thead><tr><th>#</th><th>Customer</th><th>Product</th><th>End</th></tr></thead><tbody>@foreach($dashboard['expiring'] as $warranty)<tr><td>{{ $warranty->warranty_number }}</td><td>{{ $warranty->customer?->full_name }}</td><td>{{ $warranty->product?->product_name }}</td><td>{{ optional($warranty->warranty_end_date)->format('Y-m-d') }}</td></tr>@endforeach</tbody></table></div></div></div>
</div>
@endsection
