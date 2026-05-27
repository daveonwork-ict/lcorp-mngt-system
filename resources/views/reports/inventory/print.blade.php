<!doctype html>
<html>
<head><meta charset="utf-8"><title>Inventory Report Print</title><style>body{font-family:Arial,sans-serif;font-size:12px}table{width:100%;border-collapse:collapse}th,td{border:1px solid #ccc;padding:6px;text-align:left}</style></head>
<body>
<h3>Inventory Summary Report</h3>
<p>Generated: {{ now()->format('Y-m-d H:i') }}</p>
<table>
<thead><tr><th>Branch</th><th>Product</th><th>Qty</th><th>Reorder</th><th>Value</th></tr></thead>
<tbody>@foreach($summary as $row)<tr><td>{{ $row->branch?->branch_name ?? $row->branch?->name }}</td><td>{{ $row->product?->product_name }}</td><td>{{ $row->quantity_available }}</td><td>{{ $row->reorder_level }}</td><td>{{ number_format((float) $row->inventory_value,2) }}</td></tr>@endforeach</tbody>
</table>
</body>
</html>
