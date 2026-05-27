@extends('layouts.app')

@section('page_title', 'Inventory Dashboard')
@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard.owner') }}">Dashboard</a></li>
<li class="breadcrumb-item active">Inventory</li>
@endsection

@section('content')
<div class="card mb-3">
    <div class="card-body">
        <form class="form-inline" method="GET">
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
    <div class="col-lg-6"><div class="card"><div class="card-header">Inventory Value per Branch</div><div class="card-body"><canvas id="valuePerBranch"></canvas></div></div></div>
    <div class="col-lg-6"><div class="card"><div class="card-header">Product Count per Category</div><div class="card-body"><canvas id="countPerCategory"></canvas></div></div></div>
</div>
@endsection

@push('scripts')
<script>
const valuePerBranch = @json($charts['inventory_value_per_branch']);
const countPerCategory = @json($charts['product_count_per_category']);
new Chart(document.getElementById('valuePerBranch'), { type: 'bar', data: { labels: valuePerBranch.map(r => 'Branch '+r.branch_id), datasets: [{ data: valuePerBranch.map(r => r.total_value) }] } });
new Chart(document.getElementById('countPerCategory'), { type: 'bar', data: { labels: countPerCategory.map(r => 'Category '+r.category_id), datasets: [{ data: countPerCategory.map(r => r.total_products) }] } });
</script>
@endpush
