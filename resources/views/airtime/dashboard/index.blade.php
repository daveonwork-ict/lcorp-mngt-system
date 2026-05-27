@extends('layouts.app')

@section('page_title', 'Airtime Dashboard')
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
    <div class="col-lg-6"><div class="card"><div class="card-header">Load Sales per Provider</div><div class="card-body"><canvas id="salesProvider"></canvas></div></div></div>
    <div class="col-lg-6"><div class="card"><div class="card-header">Daily Load Sales Trend</div><div class="card-body"><canvas id="dailySales"></canvas></div></div></div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">Low Wallet List</div>
            <div class="card-body table-responsive p-0">
                <table class="table table-sm mb-0"><thead><tr><th>Wallet</th><th>Branch</th><th>Provider</th><th>Balance</th></tr></thead><tbody>
                @foreach($tables['low_wallets'] as $wallet)
                    <tr><td>{{ $wallet->wallet_number }}</td><td>{{ $wallet->branch?->name }}</td><td>{{ $wallet->provider?->provider_name }}</td><td>{{ number_format($wallet->current_balance,2) }}</td></tr>
                @endforeach
                </tbody></table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">Suspicious Transactions</div>
            <div class="card-body table-responsive p-0">
                <table class="table table-sm mb-0"><thead><tr><th>Type</th><th>Branch</th><th>Provider</th><th>Message</th></tr></thead><tbody>
                @foreach($tables['suspicious'] as $alert)
                    <tr><td>{{ $alert->alert_type }}</td><td>{{ $alert->branch?->name }}</td><td>{{ $alert->provider?->provider_name }}</td><td>{{ $alert->message }}</td></tr>
                @endforeach
                </tbody></table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const perProvider = @json($charts['sales_per_provider']);
const dailySales = @json($charts['daily_sales_trend']);
new Chart(document.getElementById('salesProvider'), { type: 'bar', data: { labels: perProvider.map(r => 'Provider '+r.provider_id), datasets: [{ data: perProvider.map(r => r.total_sales), backgroundColor: '#17a2b8' }] } });
new Chart(document.getElementById('dailySales'), { type: 'line', data: { labels: dailySales.map(r => r.day), datasets: [{ data: dailySales.map(r => r.total_sales), borderColor: '#28a745', fill: false }] } });
</script>
@endpush
