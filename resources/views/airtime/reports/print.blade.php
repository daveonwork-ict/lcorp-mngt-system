<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Airtime Report Print</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        .header { margin-bottom: 12px; }
    </style>
</head>
<body>
<div class="header">
    <h2>Airtime Transactions Report</h2>
    <p>Generated at: {{ now() }}</p>
</div>
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Branch</th>
            <th>Provider</th>
            <th>Mobile</th>
            <th>Load Amount</th>
            <th>Commission</th>
            <th>Status</th>
            <th>Processed At</th>
        </tr>
    </thead>
    <tbody>
        @foreach($transactions as $tx)
            <tr>
                <td>{{ $tx->transaction_number }}</td>
                <td>{{ $tx->branch?->name }}</td>
                <td>{{ $tx->provider?->provider_name }}</td>
                <td>{{ $tx->customer_mobile_number }}</td>
                <td>{{ number_format($tx->load_amount, 2) }}</td>
                <td>{{ number_format($tx->commission_amount, 2) }}</td>
                <td>{{ ucfirst($tx->transaction_status) }}</td>
                <td>{{ $tx->processed_at }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
<script>
window.onload = function () { window.print(); };
</script>
</body>
</html>
