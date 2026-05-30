@extends('layouts.app')

@section('page_title', 'HR Dashboard')
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard.owner') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">HR Dashboard</li>
@endsection

@section('content')
<div class="row">
    @foreach ($metrics as $metric)
        <div class="col-lg-3 col-md-4 col-sm-6">
            <div class="small-box bg-white border metric-card">
                <div class="inner">
                    <p class="mb-1 text-muted">{{ $metric['label'] }}</p>
                    <h4>{{ $metric['value'] }}</h4>
                    <span class="text-xs text-info">{{ $metric['trend'] }}</span>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row">
    @foreach ($charts as $chartKey => $chart)
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header"><h3 class="card-title text-capitalize">{{ str_replace('_', ' ', $chartKey) }}</h3></div>
                <div class="card-body chart-wrap"><canvas id="{{ $chartKey }}"></canvas></div>
            </div>
        </div>
    @endforeach
</div>

<div class="row">
    @foreach ($tables as $tableName => $rows)
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header"><h3 class="card-title text-capitalize">{{ str_replace('_', ' ', $tableName) }}</h3></div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap mb-0">
                        <thead>
                            <tr>
                                @foreach (array_keys($rows->first() ?? []) as $column)
                                    <th>{{ ucfirst($column) }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $row)
                                <tr>
                                    @foreach ($row as $cell)
                                        <td>{{ $cell }}</td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr><td class="text-center text-muted">No data available.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection

@push('scripts')
<script>
const datasets = @json($charts);
Object.entries(datasets).forEach(([key, chart]) => {
    const el = document.getElementById(key);
    if (!el) return;

    new Chart(el, {
        type: 'bar',
        data: {
            labels: chart.labels,
            datasets: [{
                label: key,
                data: chart.values,
                backgroundColor: 'rgba(16, 185, 129, 0.35)',
                borderColor: 'rgba(5, 150, 105, 1)',
                borderWidth: 1,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
        },
    });
});
</script>
@endpush
