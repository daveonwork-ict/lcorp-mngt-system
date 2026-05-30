<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>HR Report Print</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #111; }
        h1 { margin-bottom: 4px; }
        .meta { margin-bottom: 16px; color: #444; }
        .summary { margin-bottom: 16px; }
        .summary span { display: inline-block; min-width: 220px; margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background: #f5f5f5; }
    </style>
</head>
<body>
    <h1>HR Report</h1>
    <div class="meta">
        Generated: {{ now()->format('Y-m-d H:i') }}<br>
        Date Range: {{ $filters['date_from'] ?? 'Any' }} to {{ $filters['date_to'] ?? 'Any' }}
    </div>

    <div class="summary">
        <span>Total Employees: {{ number_format($summary['total_employees']) }}</span>
        <span>Active Employees: {{ number_format($summary['active_employees']) }}</span>
        <span>Present Logs: {{ number_format($summary['present_logs']) }}</span>
        <span>Pending Leaves: {{ number_format($summary['pending_leaves']) }}</span>
        <span>Pending Overtime: {{ number_format($summary['pending_overtime']) }}</span>
        <span>Payroll Net Total: {{ number_format((float) $summary['payroll_net_total'], 2) }}</span>
        <span>Loan Balance Total: {{ number_format((float) $summary['loan_balance_total'], 2) }}</span>
        <span>Cash Advance Balance Total: {{ number_format((float) $summary['cash_advance_balance_total'], 2) }}</span>
    </div>

    <table>
        <thead>
            <tr>
                <th>Section</th>
                <th>Reference</th>
                <th>Employee/Period</th>
                <th>Branch</th>
                <th>Date</th>
                <th>Status</th>
                <th>Amount</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row['section'] }}</td>
                    <td>{{ $row['reference'] }}</td>
                    <td>{{ $row['employee'] }}</td>
                    <td>{{ $row['branch'] }}</td>
                    <td>{{ $row['date'] }}</td>
                    <td>{{ $row['status'] }}</td>
                    <td>{{ $row['amount'] }}</td>
                    <td>{{ $row['extra'] }}</td>
                </tr>
            @empty
                <tr><td colspan="8">No records found.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
