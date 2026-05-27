@extends('layouts.app')

@section('page_title', 'Cash Flow Dashboard')
@section('content')
<div class="row">
    @foreach($dashboard['cards'] as $label => $value)
        <div class="col-md-4 col-lg-3 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted text-uppercase small">{{ str_replace('_', ' ', $label) }}</div>
                    <div class="h4 mb-0">{{ is_numeric($value) ? number_format($value, 2) : $value }}</div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="card mb-3">
    <div class="card-header">Filter</div>
    <div class="card-body">
        <form method="GET" class="form-row">
            <div class="col-md-4">
                <select class="form-control" name="branch_id">
                    <option value="">All branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" @selected(($filters['branch_id'] ?? null) == $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary btn-block">Apply</button>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-header">Recent Cash In</div>
            <div class="card-body table-responsive p-0">
                <table class="table table-sm mb-0"><thead><tr><th>No</th><th>Type</th><th>Amount</th></tr></thead><tbody>
                    @foreach($dashboard['recent']['cash_ins'] as $entry)
                        <tr><td>{{ $entry->cash_in_number }}</td><td>{{ $entry->source_type }}</td><td>{{ number_format($entry->amount,2) }}</td></tr>
                    @endforeach
                </tbody></table>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-header">Recent Cash Out</div>
            <div class="card-body table-responsive p-0">
                <table class="table table-sm mb-0"><thead><tr><th>No</th><th>Type</th><th>Amount</th></tr></thead><tbody>
                    @foreach($dashboard['recent']['cash_outs'] as $entry)
                        <tr><td>{{ $entry->cash_out_number }}</td><td>{{ $entry->source_type }}</td><td>{{ number_format($entry->amount,2) }}</td></tr>
                    @endforeach
                </tbody></table>
            </div>
        </div>
    </div>
</div>
@endsection
