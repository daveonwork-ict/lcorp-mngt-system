<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Financial Report Print</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
    </style>
</head>
<body>
<h2>Financial Report</h2>
<p>Generated at: {{ now() }}</p>
<table>
    <thead>
        <tr>
            <th>Expense #</th>
            <th>Date</th>
            <th>Branch</th>
            <th>Category</th>
            <th>Payee</th>
            <th>Amount</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($reports['expenses'] as $expense)
            <tr>
                <td>{{ $expense->expense_number }}</td>
                <td>{{ optional($expense->expense_date)->format('Y-m-d') }}</td>
                <td>{{ $expense->branch?->name }}</td>
                <td>{{ $expense->category?->category_name }}</td>
                <td>{{ $expense->vendor_or_payee }}</td>
                <td>{{ number_format($expense->amount,2) }}</td>
                <td>{{ ucfirst($expense->status) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
<script>
window.onload = function () { window.print(); };
</script>
</body>
</html>
