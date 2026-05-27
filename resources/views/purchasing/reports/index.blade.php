@extends('layouts.app')

@section('page_title', 'Purchasing Reports')
@section('content')
<div class="mb-3">
    <a class="btn btn-outline-primary" href="{{ route('purchasing.reports.export.csv', request()->query()) }}">Export Payables CSV</a>
</div>

<div class="card mb-3">
    <div class="card-header">Purchase Request Report</div>
    <div class="table-responsive p-0"><table class="table table-sm mb-0"><thead><tr><th>Request #</th><th>Date</th><th>Branch</th><th>Status</th></tr></thead><tbody>@forelse($purchaseRequests as $row)<tr><td>{{ $row->request_number }}</td><td>{{ $row->request_date }}</td><td>{{ $row->branch?->branch_name ?? $row->branch?->name }}</td><td>{{ ucfirst(str_replace('_', ' ', $row->status)) }}</td></tr>@empty<tr><td colspan="4" class="text-center text-muted">No data.</td></tr>@endforelse</tbody></table></div>
</div>

<div class="card mb-3">
    <div class="card-header">Payable Aging</div>
    <div class="table-responsive p-0"><table class="table table-sm mb-0"><thead><tr><th>Payable #</th><th>Supplier</th><th>Due Date</th><th>Balance</th></tr></thead><tbody>@forelse($payables as $row)<tr><td>{{ $row->payable_number }}</td><td>{{ $row->supplier?->supplier_name }}</td><td>{{ $row->due_date }}</td><td>{{ number_format((float) $row->balance_amount, 2) }}</td></tr>@empty<tr><td colspan="4" class="text-center text-muted">No data.</td></tr>@endforelse</tbody></table></div>
</div>

<div class="card">
    <div class="card-header">Office Supply Usage</div>
    <div class="table-responsive p-0"><table class="table table-sm mb-0"><thead><tr><th>Issuance #</th><th>Date</th><th>Branch</th><th>Employee</th><th>Status</th></tr></thead><tbody>@forelse($issuances as $row)<tr><td>{{ $row->issuance_number }}</td><td>{{ $row->issue_date }}</td><td>{{ $row->branch?->branch_name ?? $row->branch?->name }}</td><td>{{ $row->recipient?->full_name ?? $row->recipient?->name }}</td><td>{{ ucfirst($row->status) }}</td></tr>@empty<tr><td colspan="5" class="text-center text-muted">No data.</td></tr>@endforelse</tbody></table></div>
</div>
@endsection
