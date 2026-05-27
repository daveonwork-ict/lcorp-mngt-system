@extends('layouts.app')

@section('page_title', 'Supplier Profile')
@section('content')
<div class="card mb-3">
    <div class="card-header">{{ $supplier->supplier_name }}</div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4"><strong>Contact:</strong> {{ $supplier->contact_person }} / {{ $supplier->contact_number }}</div>
            <div class="col-md-4"><strong>Email:</strong> {{ $supplier->email }}</div>
            <div class="col-md-4"><strong>Status:</strong> {{ ucfirst($supplier->status) }}</div>
        </div>
        <div class="mt-2"><strong>Address:</strong> {{ $supplier->address }}</div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-3"><div class="card"><div class="card-body"><small>Total PO</small><div class="h5 mb-0">{{ $profile['total_purchase_orders'] }}</div></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><small>Approved PO</small><div class="h5 mb-0">{{ $profile['approved_purchase_orders'] }}</div></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><small>Active Payables</small><div class="h5 mb-0">{{ number_format((float) $profile['active_payables'], 2) }}</div></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><small>MTD Payments</small><div class="h5 mb-0">{{ number_format((float) $profile['payments_mtd'], 2) }}</div></div></div></div>
</div>

<div class="card">
    <div class="card-header">Recent Purchase Orders</div>
    <div class="table-responsive p-0">
        <table class="table table-sm mb-0">
            <thead><tr><th>PO #</th><th>Date</th><th>Status</th><th>Total</th></tr></thead>
            <tbody>@forelse($recentOrders as $order)<tr><td>{{ $order->po_number }}</td><td>{{ $order->po_date }}</td><td>{{ ucfirst($order->status) }}</td><td>{{ number_format((float) $order->total_amount, 2) }}</td></tr>@empty<tr><td colspan="4" class="text-center text-muted">No PO records yet.</td></tr>@endforelse</tbody>
        </table>
    </div>
</div>
@endsection
