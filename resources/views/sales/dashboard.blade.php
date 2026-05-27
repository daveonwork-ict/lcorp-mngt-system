@extends('layouts.app')

@section('page_title', 'Sales Dashboard')
@section('content')
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="form-inline">
            <label class="mr-2">Branch</label>
            <select class="form-control mr-2" name="branch_id">
                <option value="">All branches</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" @selected($selectedBranchId === $branch->id)>{{ $branch->name }}</option>
                @endforeach
            </select>
            <button class="btn btn-outline-primary">Apply</button>
        </form>
    </div>
</div>

<div class="row">
    @foreach($cards as $card)
        <div class="col-lg-3 col-md-4 col-sm-6">
            <div class="small-box bg-white border">
                <div class="inner"><p>{{ $card['label'] }}</p><h4>{{ $card['value'] }}</h4></div>
            </div>
        </div>
    @endforeach
</div>

<div class="row">
    <div class="col-lg-6"><div class="card"><div class="card-header">Sales Trend</div><div class="card-body"><canvas id="salesTrend"></canvas></div></div></div>
    <div class="col-lg-6"><div class="card"><div class="card-header">Sales by Branch</div><div class="card-body"><canvas id="salesBranch"></canvas></div></div></div>
</div>
@endsection

@push('scripts')
<script>
const trend = @json($charts['sales_trend']);
const byBranch = @json($charts['sales_by_branch']);
new Chart(document.getElementById('salesTrend'), { type: 'line', data: { labels: trend.map(r => r.sales_date), datasets: [{ data: trend.map(r => r.total_sales), borderColor: '#007bff', fill: false }] } });
new Chart(document.getElementById('salesBranch'), { type: 'bar', data: { labels: byBranch.map(r => 'Branch '+r.branch_id), datasets: [{ data: byBranch.map(r => r.total_sales), backgroundColor: '#28a745' }] } });
</script>
@endpush
