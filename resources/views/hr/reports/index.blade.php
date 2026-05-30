@extends('layouts.app')

@section('page_title', 'HR Reports')
@section('content')
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="form-row">
            <div class="col-md-3 mb-2"><input type="date" class="form-control" name="date_from" value="{{ $filters['date_from'] ?? '' }}"></div>
            <div class="col-md-3 mb-2"><input type="date" class="form-control" name="date_to" value="{{ $filters['date_to'] ?? '' }}"></div>
            <div class="col-md-3 mb-2">
                <select class="form-control" name="branch_id">
                    <option value="">Branch</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" @selected(($filters['branch_id'] ?? null) == $branch->id)>{{ $branch->branch_name ?? $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mb-2 text-right">
                <button class="btn btn-outline-primary">Apply Filters</button>
                <a class="btn btn-outline-success" href="{{ route('hr.reports.export-csv', request()->query()) }}">Export CSV</a>
                <a class="btn btn-outline-success" href="{{ route('hr.reports.export-excel', request()->query()) }}">Export Excel</a>
                <a class="btn btn-outline-secondary" target="_blank" href="{{ route('hr.reports.print', request()->query()) }}">Print</a>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-md-3"><div class="small-box bg-white border"><div class="inner"><p>Total Employees</p><h4>{{ number_format($summary['total_employees']) }}</h4></div></div></div>
    <div class="col-md-3"><div class="small-box bg-white border"><div class="inner"><p>Active Employees</p><h4>{{ number_format($summary['active_employees']) }}</h4></div></div></div>
    <div class="col-md-3"><div class="small-box bg-white border"><div class="inner"><p>Present Logs</p><h4>{{ number_format($summary['present_logs']) }}</h4></div></div></div>
    <div class="col-md-3"><div class="small-box bg-white border"><div class="inner"><p>Pending Leaves</p><h4>{{ number_format($summary['pending_leaves']) }}</h4></div></div></div>
    <div class="col-md-3"><div class="small-box bg-white border"><div class="inner"><p>Pending Overtime</p><h4>{{ number_format($summary['pending_overtime']) }}</h4></div></div></div>
    <div class="col-md-3"><div class="small-box bg-white border"><div class="inner"><p>Payroll Net Total</p><h4>{{ number_format((float) $summary['payroll_net_total'], 2) }}</h4></div></div></div>
    <div class="col-md-3"><div class="small-box bg-white border"><div class="inner"><p>Loan Balance</p><h4>{{ number_format((float) $summary['loan_balance_total'], 2) }}</h4></div></div></div>
    <div class="col-md-3"><div class="small-box bg-white border"><div class="inner"><p>Cash Advance Balance</p><h4>{{ number_format((float) $summary['cash_advance_balance_total'], 2) }}</h4></div></div></div>
</div>

<div class="card mb-3">
    <div class="card-header">Latest Payroll Runs</div>
    <div class="table-responsive p-0">
        <table class="table table-sm mb-0">
            <thead><tr><th>Run #</th><th>Period</th><th>Branch</th><th>Status</th><th>Net Pay</th><th>Date</th></tr></thead>
            <tbody>
                @forelse($sections['payroll_runs'] as $run)
                    <tr>
                        <td>#{{ $run->id }}</td>
                        <td>{{ $run->period?->period_code }}</td>
                        <td>{{ $run->branch?->branch_name ?? $run->branch?->name }}</td>
                        <td>{{ ucfirst($run->status) }}</td>
                        <td>{{ number_format((float) $run->total_net_pay, 2) }}</td>
                        <td>{{ optional($run->created_at)->format('Y-m-d') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted">No payroll runs found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card mb-3">
            <div class="card-header">Leave Requests</div>
            <div class="table-responsive p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Employee</th><th>Type</th><th>Start</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($sections['leaves'] as $leave)
                            <tr>
                                <td>{{ $leave->user?->display_name }}</td>
                                <td>{{ strtoupper($leave->leave_type) }}</td>
                                <td>{{ optional($leave->start_date)->format('Y-m-d') }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $leave->status)) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">No leave requests found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card mb-3">
            <div class="card-header">Attendance Logs</div>
            <div class="table-responsive p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Employee</th><th>Date</th><th>Status</th><th>Late</th></tr></thead>
                    <tbody>
                        @forelse($sections['attendance'] as $log)
                            <tr>
                                <td>{{ $log->user?->display_name }}</td>
                                <td>{{ optional($log->attendance_date)->format('Y-m-d') }}</td>
                                <td>{{ ucfirst($log->attendance_status) }}</td>
                                <td>{{ (int) $log->late_minutes }} mins</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">No attendance logs found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card mb-3">
            <div class="card-header">Loan Balances</div>
            <div class="table-responsive p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Loan #</th><th>Employee</th><th>Status</th><th>Remaining</th></tr></thead>
                    <tbody>
                        @forelse($sections['loan_balances'] as $loan)
                            <tr>
                                <td>{{ $loan->loan_number }}</td>
                                <td>{{ $loan->user?->display_name }}</td>
                                <td>{{ ucfirst($loan->status) }}</td>
                                <td>{{ number_format((float) $loan->remaining_balance, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">No loan balances found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card mb-3">
            <div class="card-header">Cash Advance Balances</div>
            <div class="table-responsive p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Ref</th><th>Employee</th><th>Status</th><th>Remaining</th></tr></thead>
                    <tbody>
                        @forelse($sections['cash_advances'] as $advance)
                            <tr>
                                <td>#{{ $advance->id }}</td>
                                <td>{{ $advance->user?->display_name }}</td>
                                <td>{{ ucfirst($advance->status) }}</td>
                                <td>{{ number_format((float) $advance->remaining_balance, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">No cash advances found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
