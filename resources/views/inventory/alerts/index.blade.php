@extends('layouts.app')
@section('page_title', 'Low Stock Alerts')
@section('content')
<div class="card mb-3"><div class="card-body text-right"><form method="POST" action="{{ route('inventory.alerts.refresh') }}">@csrf<button class="btn btn-outline-primary">Refresh Alerts</button></form></div></div>
<div class="card"><div class="card-body table-responsive p-0"><table class="table table-sm"><thead><tr><th>Branch</th><th>Product</th><th>Type</th><th>Severity</th><th>Message</th><th>Status</th></tr></thead><tbody>@foreach($alerts as $alert)<tr><td>{{ $alert->branch?->name }}</td><td>{{ $alert->product?->product_name }}</td><td>{{ $alert->alert_type }}</td><td>{{ ucfirst($alert->severity) }}</td><td>{{ $alert->message }}</td><td>@if($alert->is_resolved)Resolved @else<form method="POST" action="{{ route('inventory.alerts.resolve',$alert) }}">@csrf<button class="btn btn-xs btn-success">Resolve</button></form>@endif</td></tr>@endforeach</tbody></table></div><div class="card-footer">{{ $alerts->links() }}</div></div>
@endsection
