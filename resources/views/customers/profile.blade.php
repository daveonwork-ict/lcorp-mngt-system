@extends('layouts.app')

@section('page_title', 'Customer Profile')
@section('content')
<div class="row">
    <div class="col-lg-4 mb-3">
        <div class="card h-100">
            <div class="card-header">Customer Info</div>
            <div class="card-body">
                <p><strong>{{ $customer->full_name }}</strong></p>
                <p>Code: {{ $customer->customer_code }}</p>
                <p>Mobile: {{ $customer->mobile_number }}</p>
                <p>Email: {{ $customer->email ?? '-' }}</p>
                <p>Type: {{ ucfirst(str_replace('_', ' ', $customer->customer_type)) }}</p>
                <p>Status: {{ ucfirst($customer->status) }}</p>
                <p>Total purchases: {{ number_format($totalPurchases, 2) }}</p>
                <p>Last purchase: {{ $lastPurchaseDate ?? '-' }}</p>
            </div>
        </div>
    </div>
    <div class="col-lg-8 mb-3">
        <div class="card h-100">
            <div class="card-header">Add Note</div>
            <div class="card-body">
                <form method="POST" action="{{ route('customers.notes.store', $customer) }}" class="form-inline mb-2">
                    @csrf
                    <input class="form-control mr-2 flex-grow-1" name="note" placeholder="Customer note" required>
                    <button class="btn btn-primary">Add</button>
                </form>
                <ul class="mb-0">
                    @foreach($customer->notes as $note)
                        <li>{{ $note->created_at }} - {{ $note->note }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">Purchase History</div>
    <div class="card-body table-responsive p-0">
        <table class="table table-sm mb-0">
            <thead><tr><th>Date</th><th>Branch</th><th>Receipt</th><th>Product</th><th>IMEI</th><th>Amount</th><th>Warranty</th></tr></thead>
            <tbody>
            @foreach($customer->sales as $sale)
                @foreach($sale->items as $item)
                    <tr>
                        <td>{{ optional($sale->sales_date)->format('Y-m-d') }}</td>
                        <td>{{ $sale->branch?->name }}</td>
                        <td>{{ $sale->sales_number }}</td>
                        <td>{{ $item->product?->product_name }}</td>
                        <td>{{ $item->imei?->imei_number ?? '-' }}</td>
                        <td>{{ number_format($item->subtotal, 2) }}</td>
                        <td>{{ $item->warranty_status ?? '-' }}</td>
                    </tr>
                @endforeach
            @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">Warranty History</div>
    <div class="card-body table-responsive p-0">
        <table class="table table-sm mb-0">
            <thead><tr><th>Warranty #</th><th>Product</th><th>Start</th><th>End</th><th>Status</th></tr></thead>
            <tbody>
            @foreach($customer->warranties as $warranty)
                <tr>
                    <td>{{ $warranty->warranty_number }}</td>
                    <td>{{ $warranty->product?->product_name }}</td>
                    <td>{{ optional($warranty->warranty_start_date)->format('Y-m-d') }}</td>
                    <td>{{ optional($warranty->warranty_end_date)->format('Y-m-d') }}</td>
                    <td>{{ ucfirst($warranty->warranty_status) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header">Warranty Claims</div>
    <div class="card-body table-responsive p-0">
        <table class="table table-sm mb-0">
            <thead><tr><th>Claim #</th><th>Date</th><th>Status</th><th>Issue</th></tr></thead>
            <tbody>
            @foreach($customer->warrantyClaims as $claim)
                <tr>
                    <td>{{ $claim->claim_number }}</td>
                    <td>{{ optional($claim->claim_date)->format('Y-m-d') }}</td>
                    <td>{{ ucfirst(str_replace('_',' ', $claim->claim_status)) }}</td>
                    <td>{{ $claim->issue_description }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
