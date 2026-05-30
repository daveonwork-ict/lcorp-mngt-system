@extends('layouts.app')

@section('page_title', 'Branch Dashboard')
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard.owner') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Branch</li>
@endsection

@section('content')
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="form-row align-items-end">
            <div class="col-md-3 col-sm-6 mb-2"><input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] ?? '' }}"></div>
            <div class="col-md-3 col-sm-6 mb-2"><input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] ?? '' }}"></div>
            <div class="col-md-4 col-sm-12 mb-2">
                <select name="branch_id" class="form-control">
                    @foreach($branches as $availableBranch)
                        <option value="{{ $availableBranch->id }}" @selected(($filters['branch_id'] ?? $branch->id) == $availableBranch->id)>{{ $availableBranch->branch_name ?? $availableBranch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 col-sm-12 mb-2"><button class="btn btn-primary btn-block touch-btn">Apply Filter</button></div>
        </form>
    </div>
</div>

@if($employeePanel)
<div class="card border-primary mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>Employee Self-Service</strong>
        <span class="text-muted small">Latest personal HR activity</span>
    </div>
    <div class="card-body">
        <div class="row mb-2">
            @foreach ($employeePanel['cards'] as $card)
                <div class="col-lg-3 col-md-6 mb-3">
                    <a href="{{ $card['url'] }}" class="text-decoration-none text-reset">
                        <div class="small-box bg-light border h-100 mb-0">
                            <div class="inner">
                                <p class="mb-1 text-muted">{{ $card['label'] }}</p>
                                <h4>{{ $card['value'] }}</h4>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

        <div class="row">
            <div class="col-lg-6 mb-3 mb-lg-0">
                <div class="card h-100 shadow-sm">
                    <div class="card-header"><strong>Latest Attendance</strong></div>
                    <div class="card-body">
                        @if($employeePanel['latest_attendance'])
                            <dl class="row mb-0">
                                <dt class="col-sm-4">Date</dt>
                                <dd class="col-sm-8">{{ optional($employeePanel['latest_attendance']->attendance_date)->format('Y-m-d') }}</dd>
                                <dt class="col-sm-4">Status</dt>
                                <dd class="col-sm-8 text-capitalize">{{ str_replace('_', ' ', $employeePanel['latest_attendance']->attendance_status) }}</dd>
                                <dt class="col-sm-4">Time In</dt>
                                <dd class="col-sm-8">{{ optional($employeePanel['latest_attendance']->time_in)->format('Y-m-d H:i') ?: 'N/A' }}</dd>
                                <dt class="col-sm-4">Time Out</dt>
                                <dd class="col-sm-8">{{ optional($employeePanel['latest_attendance']->time_out)->format('Y-m-d H:i') ?: 'N/A' }}</dd>
                                <dt class="col-sm-4">Branch</dt>
                                <dd class="col-sm-8">{{ $employeePanel['latest_attendance']->branch?->branch_name ?? $employeePanel['latest_attendance']->branch?->name ?? 'N/A' }}</dd>
                            </dl>
                        @else
                            <p class="text-muted mb-0">No attendance record available yet.</p>
                        @endif
                    </div>
                    <div class="card-footer bg-white text-right">
                        <a href="{{ route('hr.attendance.index') }}" class="btn btn-sm btn-outline-primary">Open Attendance</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-header"><strong>Latest Payslip</strong></div>
                    <div class="card-body">
                        @if($employeePanel['latest_payslip'])
                            <dl class="row mb-0">
                                <dt class="col-sm-4">Payslip #</dt>
                                <dd class="col-sm-8">{{ $employeePanel['latest_payslip']->payslip_number }}</dd>
                                <dt class="col-sm-4">Period</dt>
                                <dd class="col-sm-8">{{ $employeePanel['latest_payslip']->payrollItem?->run?->period?->period_code ?? 'N/A' }}</dd>
                                <dt class="col-sm-4">Generated</dt>
                                <dd class="col-sm-8">{{ optional($employeePanel['latest_payslip']->generated_at)->format('Y-m-d H:i') ?: 'N/A' }}</dd>
                                <dt class="col-sm-4">Net Pay</dt>
                                <dd class="col-sm-8">{{ number_format((float) ($employeePanel['latest_payslip']->payrollItem?->net_pay ?? 0), 2) }}</dd>
                            </dl>
                        @else
                            <p class="text-muted mb-0">No payslip available yet.</p>
                        @endif
                    </div>
                    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                        <a href="{{ route('hr.payslips.index') }}" class="btn btn-sm btn-outline-primary">Open Payslips</a>
                        @if($employeePanel['latest_payslip'])
                            <a href="{{ route('hr.payslips.download', $employeePanel['latest_payslip']) }}" class="btn btn-sm btn-primary">Download Latest</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row">
    @foreach ($summary['cards'] as $metric)
        <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
            <div class="small-box bg-white border metric-card">
                <div class="inner">
                    <p class="mb-1 text-muted">{{ $metric['label'] }}</p>
                    <h4>{{ $metric['value'] }}</h4>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row">
    @foreach ($summary['charts'] as $chartKey => $points)
        <div class="col-lg-6 mb-3">
            <div class="card chart-card">
                <div class="card-header"><strong>{{ ucwords(str_replace('_', ' ', $chartKey)) }}</strong></div>
                <div class="card-body"><div style="height: 260px"><canvas id="{{ $chartKey }}"></canvas></div></div>
            </div>
        </div>
    @endforeach
</div>

<div class="row">
    <div class="col-lg-6 mb-3">
        <div class="card h-100">
            <div class="card-header"><strong>KPI Alerts</strong></div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($summary['kpi_alerts'] as $alert)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>{{ $alert['message'] }}</span>
                            <span class="badge badge-{{ $alert['severity'] === 'high' ? 'danger' : 'warning' }}">{{ strtoupper($alert['severity']) }}</span>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-muted">No active KPI alerts.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-3">
        <div class="card h-100">
            <div class="card-header"><strong>Recent Branch Sales</strong></div>
            <div class="card-body table-responsive p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Sale #</th><th>Cashier</th><th>Total</th></tr></thead>
                    <tbody>
                        @foreach($summary['tables']['recent_branch_sales'] as $sale)
                            <tr>
                                <td>{{ $sale->sales_number }}</td>
                                <td>{{ $sale->cashier?->display_name }}</td>
                                <td>{{ number_format((float) $sale->total_amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const chartPayloads = @json($summary['charts']);
Object.entries(chartPayloads).forEach(([chartKey, points]) => {
    const target = document.getElementById(chartKey);
    if (!target) return;

    const labels = points.map(point => point.label ?? 'N/A');
    const values = points.map(point => Number(point.value ?? 0));

    new Chart(target, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: chartKey.replaceAll('_', ' '),
                data: values,
                borderColor: 'rgba(40,167,69,1)',
                backgroundColor: 'rgba(40,167,69,0.2)',
                tension: 0.3,
                fill: true
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
});
</script>
@endpush
