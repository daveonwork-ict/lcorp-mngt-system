<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Airtime Report Print</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: "Segoe UI", Tahoma, Arial, sans-serif;
            font-size: 12px;
            color: #1f2933;
            margin: 0;
            padding: 18px;
            background: #ffffff;
        }
        .header {
            border: 1px solid #d9e2ec;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 12px;
        }
        .title-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 8px;
        }
        .title {
            font-size: 20px;
            font-weight: 700;
            margin: 0;
            letter-spacing: 0.02em;
        }
        .subtitle {
            margin: 3px 0 0 0;
            font-size: 12px;
            color: #52606d;
        }
        .meta {
            text-align: right;
            font-size: 11px;
            color: #52606d;
            line-height: 1.45;
        }
        .summary {
            display: table;
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px 0;
            margin: 0 -8px;
        }
        .summary .cell {
            display: table-cell;
            width: 25%;
            border: 1px solid #d9e2ec;
            border-radius: 8px;
            padding: 8px;
            vertical-align: top;
            background: #f8fbff;
        }
        .summary .label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #52606d;
            margin-bottom: 3px;
        }
        .summary .value {
            font-size: 14px;
            font-weight: 700;
            color: #102a43;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #d9e2ec;
        }
        thead th {
            background: #f0f4f8;
            color: #243b53;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            border: 1px solid #d9e2ec;
            padding: 7px;
            text-align: left;
        }
        tbody td {
            border: 1px solid #d9e2ec;
            padding: 7px;
            vertical-align: top;
        }
        tbody tr:nth-child(even) {
            background: #fafbfc;
        }
        .text-right {
            text-align: right;
        }
        .status {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            border: 1px solid #d9e2ec;
        }
        .status-successful { background: #d4edda; color: #175c2c; }
        .status-pending { background: #fff3cd; color: #7c5700; }
        .status-failed, .status-cancelled { background: #f8d7da; color: #7c1d25; }
        .status-reversed { background: #e2e3e5; color: #383d41; }
        .empty {
            text-align: center;
            color: #52606d;
            padding: 18px 8px;
        }
        @page { size: auto; margin: 10mm; }
    </style>
</head>
<body>
@php
    $rows = $transactions->getCollection();
    $totalLoad = $rows->sum('load_amount');
    $totalCommission = $rows->sum('commission_amount');
@endphp

<div class="header">
    <div class="title-row">
        <div>
            <h1 class="title">Airtime Transactions Report</h1>
            <p class="subtitle">Operational print summary for filtered airtime transactions</p>
        </div>
        <div class="meta">
            Generated At: {{ now() }}<br>
            Rows: {{ number_format($rows->count()) }}
        </div>
    </div>

    <div class="summary">
        <div class="cell">
            <div class="label">Visible Transactions</div>
            <div class="value">{{ number_format($rows->count()) }}</div>
        </div>
        <div class="cell">
            <div class="label">Visible Load Total</div>
            <div class="value">PHP {{ number_format($totalLoad, 2) }}</div>
        </div>
        <div class="cell">
            <div class="label">Visible Commission</div>
            <div class="value">PHP {{ number_format($totalCommission, 2) }}</div>
        </div>
        <div class="cell">
            <div class="label">Filter Window</div>
            <div class="value" style="font-size:12px;">
                {{ $filters['date_from'] ?? 'Any' }} to {{ $filters['date_to'] ?? 'Any' }}
            </div>
        </div>
    </div>
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
        @forelse($rows as $tx)
            <tr>
                <td>{{ $tx->transaction_number }}</td>
                <td>{{ $tx->branch?->name }}</td>
                <td>{{ $tx->provider?->provider_name }}</td>
                <td>{{ $tx->customer_mobile_number }}</td>
                <td class="text-right">{{ number_format($tx->load_amount, 2) }}</td>
                <td class="text-right">{{ number_format($tx->commission_amount, 2) }}</td>
                <td>
                    <span class="status status-{{ $tx->transaction_status }}">{{ $tx->transaction_status }}</span>
                </td>
                <td>{{ $tx->processed_at }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="empty">No transaction rows available for the selected filters.</td>
            </tr>
        @endforelse
    </tbody>
</table>
<script>
window.onload = function () { window.print(); };
</script>
</body>
</html>
