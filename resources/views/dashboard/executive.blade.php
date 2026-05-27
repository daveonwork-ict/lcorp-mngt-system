@extends('layouts.app')

@section('page_title', 'Executive Dashboard')
@section('content')
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="form-row align-items-end">
            <div class="col-md-3 col-sm-6 mb-2"><input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] ?? '' }}"></div>
            <div class="col-md-3 col-sm-6 mb-2"><input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] ?? '' }}"></div>
            <div class="col-md-4 col-sm-12 mb-2"><select name="branch_id" class="form-control"><option value="">All branches</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected(($filters['branch_id'] ?? null)==$branch->id)>{{ $branch->branch_name ?? $branch->name }}</option>@endforeach</select></div>
            <div class="col-md-2 col-sm-12 mb-2"><button class="btn btn-primary btn-block touch-btn">Apply Filter</button></div>
        </form>
    </div>
</div>

<div class="row">
    @foreach($summary['cards'] as $card)
        <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
            <div class="small-box bg-white border">
                <div class="inner">
                    <p class="mb-1 text-muted">{{ $card['label'] }}</p>
                    <h4>{{ $card['value'] }}</h4>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row">
    @foreach($summary['charts'] as $chartKey => $points)
        <div class="col-lg-6 mb-3">
            <div class="card h-100 chart-card">
                <div class="card-header"><strong>{{ ucwords(str_replace('_', ' ', $chartKey)) }}</strong></div>
                <div class="card-body"><div style="height:260px"><canvas id="{{ $chartKey }}"></canvas></div></div>
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
            <div class="card-header"><strong>Recent Sales</strong></div>
            <div class="card-body table-responsive p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Sale #</th><th>Branch</th><th>Cashier</th><th>Total</th></tr></thead>
                    <tbody>
                    @foreach($summary['tables']['recent_sales'] as $sale)
                        <tr>
                            <td>{{ $sale->sales_number }}</td>
                            <td>{{ $sale->branch?->branch_name ?? $sale->branch?->name }}</td>
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
                borderColor: 'rgba(13,110,253,1)',
                backgroundColor: 'rgba(13,110,253,0.2)',
                tension: 0.3,
                fill: true
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
});
</script>
@endpush
