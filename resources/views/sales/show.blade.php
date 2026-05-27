@extends('layouts.app')

@section('page_title', 'Sale Details')
@section('content')
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>{{ $sale->sales_number }}</strong>
        <div>
            <a class="btn btn-sm btn-outline-primary" href="{{ route('sales.receipt', $sale) }}">Receipt</a>
            @if(auth()->user()?->hasPermission('reprint_receipt'))
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('sales.receipt.reprint', $sale) }}">Reprint</a>
            @endif
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3"><strong>Branch:</strong> {{ $sale->branch?->name }}</div>
            <div class="col-md-3"><strong>Cashier:</strong> {{ $sale->cashier?->display_name }}</div>
            <div class="col-md-3"><strong>Status:</strong> {{ str($sale->sales_status)->replace('_', ' ')->title() }}</div>
            <div class="col-md-3"><strong>Payment:</strong> {{ str($sale->payment_status)->title() }}</div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header">Items</div>
            <div class="card-body table-responsive p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Product</th><th>IMEI</th><th>Qty</th><th>Price</th><th>Discount</th><th>Subtotal</th></tr></thead>
                    <tbody>
                        @foreach($sale->items as $item)
                            <tr>
                                <td>{{ $item->product?->product_name }}</td>
                                <td>{{ $item->imei?->imei_number }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ number_format($item->selling_price, 2) }}</td>
                                <td>{{ number_format($item->discount_amount, 2) }}</td>
                                <td>{{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Payments</div>
            <div class="card-body table-responsive p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Method</th><th>Reference</th><th>Amount</th><th>Date</th></tr></thead>
                    <tbody>
                        @foreach($sale->payments as $payment)
                            <tr>
                                <td>{{ $payment->paymentMethod?->payment_method_name }}</td>
                                <td>{{ $payment->payment_reference }}</td>
                                <td>{{ number_format($payment->amount, 2) }}</td>
                                <td>{{ $payment->received_at }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header">Summary</div>
            <div class="card-body">
                <p>Subtotal: <strong>{{ number_format($sale->subtotal_amount, 2) }}</strong></p>
                <p>Discount: <strong>{{ number_format($sale->discount_amount, 2) }}</strong></p>
                <p>Total: <strong>{{ number_format($sale->total_amount, 2) }}</strong></p>
                <p>Paid: <strong>{{ number_format($sale->paid_amount, 2) }}</strong></p>
                <p>Change: <strong>{{ number_format($sale->change_amount, 2) }}</strong></p>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">Add Payment</div>
            <div class="card-body">
                <form method="POST" action="{{ route('sales.payments.store', $sale) }}">
                    @csrf
                    <div class="form-group"><label>Method ID</label><input class="form-control" type="number" name="payments[0][payment_method_id]" required></div>
                    <div class="form-group"><label>Reference</label><input class="form-control" name="payments[0][payment_reference]"></div>
                    <div class="form-group"><label>Amount</label><input class="form-control" type="number" step="0.01" name="payments[0][amount]" required></div>
                    <button class="btn btn-outline-primary btn-block">Save Payment</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Actions</div>
            <div class="card-body">
                <form method="POST" action="{{ route('sales.voids.store', $sale) }}" class="mb-2">
                    @csrf
                    <input class="form-control mb-2" name="reason" placeholder="Void reason" required>
                    <button class="btn btn-warning btn-block">Request Void</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
