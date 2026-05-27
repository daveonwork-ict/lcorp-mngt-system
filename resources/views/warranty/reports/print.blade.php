<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Warranty Reports Print</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
    </style>
</head>
<body>
<h2>Warranty Reports</h2>
<p>Generated at: {{ now() }}</p>
<h3>Warranties</h3>
<table>
    <thead><tr><th>#</th><th>Customer</th><th>Product</th><th>Start</th><th>End</th><th>Status</th></tr></thead>
    <tbody>
    @foreach($warranties as $warranty)
        <tr>
            <td>{{ $warranty->warranty_number }}</td>
            <td>{{ $warranty->customer?->full_name }}</td>
            <td>{{ $warranty->product?->product_name }}</td>
            <td>{{ optional($warranty->warranty_start_date)->format('Y-m-d') }}</td>
            <td>{{ optional($warranty->warranty_end_date)->format('Y-m-d') }}</td>
            <td>{{ ucfirst($warranty->warranty_status) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
<h3>Claims</h3>
<table>
    <thead><tr><th>Claim</th><th>Warranty</th><th>Customer</th><th>Status</th><th>Date</th></tr></thead>
    <tbody>
    @foreach($claims as $claim)
        <tr>
            <td>{{ $claim->claim_number }}</td>
            <td>{{ $claim->warranty?->warranty_number }}</td>
            <td>{{ $claim->customer?->full_name }}</td>
            <td>{{ ucfirst(str_replace('_',' ', $claim->claim_status)) }}</td>
            <td>{{ optional($claim->claim_date)->format('Y-m-d') }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
<script>
window.onload = function () { window.print(); };
</script>
</body>
</html>
