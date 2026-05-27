<!doctype html>
<html>
<head><meta charset="utf-8"><title>Sales Report Print</title><style>body{font-family:Arial,sans-serif;font-size:12px}table{width:100%;border-collapse:collapse}th,td{border:1px solid #ccc;padding:6px;text-align:left}</style></head>
<body>
<h3>Sales Report</h3>
<p>Generated: {{ now()->format('Y-m-d H:i') }}</p>
<p>Date range: {{ $filters['date_from'] ?? 'N/A' }} to {{ $filters['date_to'] ?? 'N/A' }}</p>
<table>
<thead><tr><th>#</th><th>Date</th><th>Branch</th><th>Cashier</th><th>Customer</th><th>Total</th><th>Status</th></tr></thead>
<tbody>@foreach($sales as $sale)<tr><td>{{ $sale->sales_number }}</td><td>{{ optional($sale->sales_date)->format('Y-m-d') }}</td><td>{{ $sale->branch?->branch_name ?? $sale->branch?->name }}</td><td>{{ $sale->cashier?->display_name }}</td><td>{{ $sale->customer?->full_name }}</td><td>{{ number_format((float) $sale->total_amount,2) }}</td><td>{{ ucfirst($sale->sales_status) }}</td></tr>@endforeach</tbody>
</table>
</body>
</html>
