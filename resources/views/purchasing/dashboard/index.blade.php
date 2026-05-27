@extends('layouts.app')

@section('page_title', 'Purchasing Dashboard')
@section('content')
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="form-row">
            <div class="col-md-4 mb-2">
                <select name="branch_id" class="form-control">
                    <option value="">All branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) $selectedBranchId === (string) $branch->id)>{{ $branch->branch_name ?? $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mb-2"><button class="btn btn-primary btn-block">Filter</button></div>
        </form>
    </div>
</div>

<div class="row">
    @foreach($cards as $label => $value)
        <div class="col-md-3 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">{{ str_replace('_', ' ', $label) }}</div>
                    <div class="h4 mb-0">{{ is_numeric($value) ? number_format((float) $value, 2) : $value }}</div>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
