<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payslip {{ $payslip->payslip_number }}</title>
    <style>
        @page {
            size: A5 landscape;
            margin: 8mm;
        }

        body {
            margin: 0;
            background: #eef2f7;
            font-family: "Segoe UI", Arial, sans-serif;
            font-size: 9.5px;
            line-height: 1.15;
            color: #1f2937;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .page {
            max-width: 780px;
            margin: 22px auto;
            background: #fff;
            border: 1px solid #d1d9e6;
            box-shadow: 0 5px 18px rgba(15, 23, 42, 0.08);
        }

        .header {
            padding: 10px 12px;
            background: linear-gradient(90deg, #0f3d67 0%, #1363a2 100%);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .header h1 {
            margin: 0;
            font-size: 15px;
            letter-spacing: 0.3px;
        }

        .header p {
            margin: 2px 0 0;
            font-size: 10px;
            opacity: 0.92;
        }

        .slip-badge {
            padding: 4px 8px;
            border-radius: 999px;
            background: #f7b801;
            color: #111827;
            font-weight: 700;
            font-size: 9px;
            letter-spacing: 0.6px;
            text-transform: uppercase;
        }

        .meta {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .meta td {
            width: 25%;
            border: 1px solid #dbe3ee;
            padding: 6px 7px;
            vertical-align: top;
        }

        .meta-label {
            display: block;
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 1px;
        }

        .meta-value {
            font-weight: 700;
            font-size: 10.5px;
            color: #111827;
        }

        .truncate {
            display: block;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .content {
            padding: 7px 8px 6px;
        }

        .section-title {
            margin: 0 0 5px;
            font-size: 9px;
            font-weight: 700;
            color: #0f3d67;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }

        table.split {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px 0;
            margin-bottom: 6px;
        }

        .split td {
            width: 50%;
            padding: 0;
            vertical-align: top;
        }

        table.grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }

        .grid th,
        .grid td {
            border: 1px solid #d5deea;
            padding: 3px 5px;
        }

        .grid th {
            background: #edf3fb;
            font-size: 8.5px;
            color: #1e293b;
            text-align: left;
        }

        .numeric {
            text-align: right;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }

        .totals {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }

        .totals td {
            border: 1px solid #d5deea;
            padding: 4px 6px;
        }

        .totals td:first-child {
            width: 72%;
            background: #f8fafc;
            font-weight: 600;
        }

        .net-row td {
            background: #e5f6ea;
            font-weight: 800;
            font-size: 10px;
        }

        .notes {
            margin-top: 5px;
            border: 1px solid #dbe3ee;
            background: #f8fafc;
            padding: 5px 6px;
            font-size: 8px;
            color: #475569;
        }

        .signatures {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }

        .signatures td {
            width: 50%;
            padding: 7px 4px 1px;
        }

        .sign-line {
            border-top: 1px solid #1f2937;
            text-align: center;
            margin-top: 10px;
            padding-top: 2px;
            font-size: 8px;
            color: #374151;
        }

        .toolbar {
            max-width: 780px;
            margin: 16px auto 0;
            text-align: right;
        }

        .toolbar button {
            border: 1px solid #1d4ed8;
            background: #2563eb;
            color: #fff;
            border-radius: 6px;
            font-size: 12px;
            padding: 8px 12px;
            cursor: pointer;
        }

        @media print {
            body {
                background: #fff;
            }

            .toolbar {
                display: none;
            }

            .page {
                margin: 0;
                border: none;
                box-shadow: none;
                max-width: none;
            }
        }
    </style>
</head>
<body>
@php
    $employee = $item?->user;
    $run = $item?->run;
    $period = $run?->period;
    $branchName = $item?->branch?->name ?? $run?->branch?->name ?? 'N/A';
    $snapshot = $item?->computation_snapshot ?? [];
@endphp

<div class="toolbar">
    <button type="button" onclick="window.print()">Print Payslip</button>
</div>

<div class="page">
    <div class="header">
        <div>
            <h1>RC Store RMS</h1>
            <p>Employee Payroll Payslip</p>
        </div>
        <div class="slip-badge">{{ $payslip->payslip_number }}</div>
    </div>

    <table class="meta">
        <tr>
            <td><span class="meta-label">Employee</span><span class="meta-value truncate">{{ $employee?->display_name ?? $employee?->full_name ?? $employee?->name ?? 'N/A' }}</span></td>
            <td><span class="meta-label">Employee Code</span><span class="meta-value truncate">{{ $employee?->employee_code ?? 'N/A' }}</span></td>
            <td><span class="meta-label">Branch</span><span class="meta-value truncate">{{ $branchName }}</span></td>
            <td><span class="meta-label">Payroll Run</span><span class="meta-value truncate">#{{ $run?->id ?? 'N/A' }}</span></td>
        </tr>
        <tr>
            <td><span class="meta-label">Payroll Period</span><span class="meta-value truncate">{{ $period?->period_code ?? 'N/A' }}</span></td>
            <td><span class="meta-label">Period Start</span><span class="meta-value truncate">{{ optional($period?->period_start)->format('M d, Y') ?? 'N/A' }}</span></td>
            <td><span class="meta-label">Period End</span><span class="meta-value truncate">{{ optional($period?->period_end)->format('M d, Y') ?? 'N/A' }}</span></td>
            <td><span class="meta-label">Generated At</span><span class="meta-value truncate">{{ optional($payslip->generated_at)->format('M d, Y h:i A') ?? now()->format('M d, Y h:i A') }}</span></td>
        </tr>
    </table>

    <div class="content">
        <table class="split">
            <tr>
                <td>
                    <div class="section-title">Earnings</div>
                    <table class="grid">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th class="numeric">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>Basic Pay</td><td class="numeric">{{ number_format((float) ($item?->basic_pay ?? 0), 2) }}</td></tr>
                            <tr><td>Overtime Pay</td><td class="numeric">{{ number_format((float) ($item?->overtime_pay ?? 0), 2) }}</td></tr>
                            <tr><td>Allowances</td><td class="numeric">{{ number_format((float) ($item?->allowances ?? 0), 2) }}</td></tr>
                            <tr><td>Holiday Pay</td><td class="numeric">{{ number_format((float) ($item?->holiday_pay ?? 0), 2) }}</td></tr>
                            <tr><td>Night Diff.</td><td class="numeric">{{ number_format((float) ($item?->night_differential_pay ?? 0), 2) }}</td></tr>
                            <tr><td>Incentives</td><td class="numeric">{{ number_format((float) ($item?->incentives ?? 0), 2) }}</td></tr>
                        </tbody>
                    </table>
                </td>
                <td>
                    <div class="section-title">Deductions</div>
                    <table class="grid">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th class="numeric">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>SSS</td><td class="numeric">{{ number_format((float) ($item?->sss_deduction ?? 0), 2) }}</td></tr>
                            <tr><td>PhilHealth</td><td class="numeric">{{ number_format((float) ($item?->philhealth_deduction ?? 0), 2) }}</td></tr>
                            <tr><td>Pag-IBIG</td><td class="numeric">{{ number_format((float) ($item?->pagibig_deduction ?? 0), 2) }}</td></tr>
                            <tr><td>Tax</td><td class="numeric">{{ number_format((float) ($item?->withholding_tax_deduction ?? 0), 2) }}</td></tr>
                            <tr><td>Loan</td><td class="numeric">{{ number_format((float) ($item?->loan_deduction ?? 0), 2) }}</td></tr>
                            <tr><td>Cash Advance</td><td class="numeric">{{ number_format((float) ($item?->cash_advance_deduction ?? 0), 2) }}</td></tr>
                            <tr><td>Other</td><td class="numeric">{{ number_format((float) ($item?->other_deduction ?? 0), 2) }}</td></tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </table>

        <table class="totals">
            <tr>
                <td>Total Gross Pay</td>
                <td class="numeric">{{ number_format((float) ($item?->gross_pay ?? 0), 2) }}</td>
            </tr>
            <tr>
                <td>Total Deductions</td>
                <td class="numeric">{{ number_format((float) ($item?->total_deductions ?? 0), 2) }}</td>
            </tr>
            <tr class="net-row">
                <td>Net Pay</td>
                <td class="numeric">{{ number_format((float) ($item?->net_pay ?? 0), 2) }}</td>
            </tr>
        </table>

        <div class="notes">
            <strong>Computation Details:</strong>
            Days Worked: {{ $snapshot['days_worked'] ?? 0 }},
            Worked Minutes: {{ $snapshot['worked_minutes'] ?? 0 }},
            Overtime Hours: {{ number_format((float) ($snapshot['overtime_hours'] ?? 0), 2) }},
            Salary Type: {{ strtoupper((string) ($snapshot['salary_type'] ?? 'n/a')) }}.
        </div>

        <table class="signatures">
            <tr>
                <td><div class="sign-line">Employee Signature</div></td>
                <td><div class="sign-line">Authorized HR/Payroll Officer</div></td>
            </tr>
        </table>
    </div>
</div>

<script>
window.addEventListener('load', function () {
    const shouldAutoPrint = new URLSearchParams(window.location.search).get('autoprint') === '1';

    if (shouldAutoPrint) {
        window.print();
    }
});
</script>
</body>
</html>
