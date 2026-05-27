@extends('layouts.app')

@section('page_title', 'Receipt')
@section('content')
<div class="card mx-auto" style="max-width: 760px;">
    <div class="card-body">
        <div class="text-center mb-3">
            <h4>{{ $setting?->store_name ?: config('app.name') }}</h4>
            <div>{{ $sale->branch?->name }}</div>
            @if($setting?->show_branch_address)
                <div>{{ $sale->branch?->address }}</div>
            @endif
        </div>

        <p><strong>Receipt #:</strong> {{ $sale->sales_number }}<br>
        <strong>Date/Time:</strong> {{ $sale->sales_date }} {{ $sale->sales_time }}<br>
        <strong>Cashier:</strong> {{ $sale->cashier?->display_name }}</p>

        <table class="table table-sm">
            <thead><tr><th>Item</th><th>IMEI</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr></thead>
            <tbody>
                @foreach($sale->items as $item)
                    <tr>
                        <td>{{ $item->product?->product_name }}</td>
                        <td>{{ $item->imei?->imei_number }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->selling_price, 2) }}</td>
                        <td>{{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="text-right">
            <p>Subtotal: {{ number_format($sale->subtotal_amount, 2) }}</p>
            <p>Discount: {{ number_format($sale->discount_amount, 2) }}</p>
            <p>Total: <strong>{{ number_format($sale->total_amount, 2) }}</strong></p>
            <p>Paid: {{ number_format($sale->paid_amount, 2) }}</p>
            <p>Change: {{ number_format($sale->change_amount, 2) }}</p>
        </div>

        <p><strong>Payment Method:</strong> {{ $sale->payments->pluck('paymentMethod.payment_method_name')->filter()->implode(', ') }}</p>
        <p><strong>Warranty Note:</strong> {{ $setting?->warranty_note ?: 'Please keep your receipt for warranty claims.' }}</p>
        <p class="text-center mt-4">{{ $setting?->thank_you_message ?: 'Thank you for your purchase.' }}</p>

        <div class="text-center mt-3">
            <button type="button" class="btn btn-outline-secondary" onclick="window.print()">Print</button>
        </div>
    </div>
</div>
@endsection
