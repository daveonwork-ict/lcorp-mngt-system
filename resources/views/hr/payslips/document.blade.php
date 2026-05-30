<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="ProgId" content="Word.Document">
    <title>Payslip {{ $payslipNumber }}</title>
    <style>
        @page {
            margin: 8mm;
            size: A5 landscape;
        }
        body {
            font-family: Calibri, Arial, sans-serif;
            color: #1f2937;
            font-size: 9.5px;
            line-height: 1.15;
            margin: 0;
        }
        .sheet {
            border: 1px solid #d1d9e6;
            overflow: hidden;
        }
        .header {
            padding: 10px 12px;
            background: #0f3d67;
            color: #fff;
        }
        .company {
            margin: 0;
            font-size: 15px;
            letter-spacing: 0.3px;
        }
        .subtitle {
            margin: 2px 0 0;
            font-size: 10px;
            opacity: 0.95;
        }
        .badge {
            float: right;
            margin-top: 1px;
            background: #f7b801;
            color: #111827;
            font-weight: 700;
            font-size: 9px;
            letter-spacing: 0.6px;
            padding: 4px 8px;
            border-radius: 999px;
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
        .label {
            display: block;
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 1px;
        }
        .value {
            font-size: 10.5px;
            font-weight: 700;
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
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #0f3d67;
            font-weight: 700;
        }
        .split {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px 0;
            margin-bottom: 6px;
        }
        .split td {
            width: 50%;
            vertical-align: top;
            padding: 0;
        }
        .grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }
        .grid th,
        .grid td {
            border: 1px solid #d9e0ea;
            padding: 3px 5px;
        }
        .grid th {
            background: #eef4fb;
            text-align: left;
            font-size: 8.5px;
            color: #1f2f46;
        }
        .numeric {
            text-align: right;
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
        }
        .summary {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        .summary td {
            padding: 4px 6px;
            border: 1px solid #d9e0ea;
        }
        .summary tr td:first-child {
            background: #f8fafc;
            font-weight: 600;
            width: 70%;
        }
        .summary .net td {
            background: #e6f4ea;
            font-weight: 800;
            font-size: 10px;
        }
        .notes {
            margin: 5px 0 1px;
            padding: 5px 6px;
            background: #f8fafc;
            border: 1px solid #dbe3ee;
            color: #4b5563;
            font-size: 8px;
        }
        .signatures {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        .signatures td {
            width: 50%;
            padding: 7px 4px 1px;
            vertical-align: top;
        }
        .sign-line {
            border-top: 1px solid #1f2937;
            margin-top: 10px;
            padding-top: 2px;
            font-size: 8px;
            color: #374151;
            text-align: center;
        }
        .footer {
            margin-top: 2px;
            font-size: 8px;
            color: #6b7280;
            text-align: right;
        }
    </style>
</head>
<body>
@php
    $employee = $item->user;
    $run = $item->run;
    $period = $run?->period;
    $branchName = $item->branch?->name ?? $run?->branch?->name ?? 'N/A';
    $snapshot = $item->computation_snapshot ?? [];
@endphp

<div class="sheet">
    <div class="header">
        <span class="badge">PAYSLIP</span>
        <h1 class="company">RC Store RMS</h1>
        <p class="subtitle">Employee Payroll Statement</p>
    </div>

    <table class="meta">
        <tr>
            <td>
                <span class="label">Payslip No.</span>
                <span class="value truncate">{{ $payslipNumber }}</span>
            </td>
            <td>
                <span class="label">Payroll Period</span>
                <span class="value truncate">{{ $period?->period_code ?? 'N/A' }}</span>
            </td>
            <td>
                <span class="label">Period Range</span>
                <span class="value truncate">{{ optional($period?->period_start)->format('M d, Y') ?? 'N/A' }} - {{ optional($period?->period_end)->format('M d, Y') ?? 'N/A' }}</span>
            </td>
            <td>
                <span class="label">Generated</span>
                <span class="value truncate">{{ $generatedAt->format('M d, Y h:i A') }}</span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="label">Employee</span>
                <span class="value truncate">{{ $employee?->display_name ?? $employee?->full_name ?? $employee?->name ?? 'N/A' }}</span>
            </td>
            <td>
                <span class="label">Employee Code</span>
                <span class="value truncate">{{ $employee?->employee_code ?? 'N/A' }}</span>
            </td>
            <td>
                <span class="label">Branch</span>
                <span class="value truncate">{{ $branchName }}</span>
            </td>
            <td>
                <span class="label">Payroll Run</span>
                <span class="value truncate">#{{ $run?->id ?? 'N/A' }}</span>
            </td>
        </tr>
    </table>

    <div class="content">
        <table class="split">
            <tr>
                <td>
                    <h2 class="section-title">Earnings</h2>
                    <table class="grid">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th class="numeric">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>Basic Pay</td><td class="numeric">{{ number_format((float) $item->basic_pay, 2) }}</td></tr>
                            <tr><td>Overtime Pay</td><td class="numeric">{{ number_format((float) $item->overtime_pay, 2) }}</td></tr>
                            <tr><td>Allowances</td><td class="numeric">{{ number_format((float) $item->allowances, 2) }}</td></tr>
                            <tr><td>Holiday Pay</td><td class="numeric">{{ number_format((float) $item->holiday_pay, 2) }}</td></tr>
                            <tr><td>Night Diff.</td><td class="numeric">{{ number_format((float) $item->night_differential_pay, 2) }}</td></tr>
                            <tr><td>Incentives</td><td class="numeric">{{ number_format((float) $item->incentives, 2) }}</td></tr>
                        </tbody>
                    </table>
                </td>
                <td>
                    <h2 class="section-title">Deductions</h2>
                    <table class="grid">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th class="numeric">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>SSS</td><td class="numeric">{{ number_format((float) $item->sss_deduction, 2) }}</td></tr>
                            <tr><td>PhilHealth</td><td class="numeric">{{ number_format((float) $item->philhealth_deduction, 2) }}</td></tr>
                            <tr><td>Pag-IBIG</td><td class="numeric">{{ number_format((float) $item->pagibig_deduction, 2) }}</td></tr>
                            <tr><td>Tax</td><td class="numeric">{{ number_format((float) $item->withholding_tax_deduction, 2) }}</td></tr>
                            <tr><td>Loan</td><td class="numeric">{{ number_format((float) $item->loan_deduction, 2) }}</td></tr>
                            <tr><td>Cash Advance</td><td class="numeric">{{ number_format((float) $item->cash_advance_deduction, 2) }}</td></tr>
                            <tr><td>Other</td><td class="numeric">{{ number_format((float) $item->other_deduction, 2) }}</td></tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </table>

        <table class="summary">
            <tr>
                <td>Total Gross Pay</td>
                <td class="numeric">{{ number_format((float) $item->gross_pay, 2) }}</td>
            </tr>
            <tr>
                <td>Total Deductions</td>
                <td class="numeric">{{ number_format((float) $item->total_deductions, 2) }}</td>
            </tr>
            <tr class="net">
                <td>Net Pay</td>
                <td class="numeric">{{ number_format((float) $item->net_pay, 2) }}</td>
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
                <td>
                    <div class="sign-line">Employee Signature</div>
                </td>
                <td>
                    <div class="sign-line">Authorized HR/Payroll Officer</div>
                </td>
            </tr>
        </table>

        <div class="footer">Generated by RC Store RMS on {{ $generatedAt->format('Y-m-d H:i:s') }}</div>
    </div>
</div>
</body>
</html>
