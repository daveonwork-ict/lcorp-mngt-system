@extends('layouts.app')

@section('page_title', 'Report Center')
@section('content')
<div class="row mb-3">
    <div class="col-lg-3 col-md-6 mb-2"><a class="btn btn-outline-primary btn-block" href="{{ route('reports.sales.index') }}">Sales Reports</a></div>
    <div class="col-lg-3 col-md-6 mb-2"><a class="btn btn-outline-primary btn-block" href="{{ route('reports.inventory.index') }}">Inventory Reports</a></div>
    <div class="col-lg-3 col-md-6 mb-2"><a class="btn btn-outline-primary btn-block" href="{{ route('airtime.reports.index') }}">Airtime Reports</a></div>
    <div class="col-lg-3 col-md-6 mb-2"><a class="btn btn-outline-primary btn-block" href="{{ route('finance.reports.index') }}">Financial Reports</a></div>
    <div class="col-lg-3 col-md-6 mb-2"><a class="btn btn-outline-primary btn-block" href="{{ route('warranty.reports.index') }}">Warranty Reports</a></div>
    <div class="col-lg-3 col-md-6 mb-2"><a class="btn btn-outline-primary btn-block" href="{{ route('reports.communication.index') }}">Communication Reports</a></div>
    <div class="col-lg-3 col-md-6 mb-2"><a class="btn btn-outline-primary btn-block" href="{{ route('reports.audit.index') }}">Audit Reports</a></div>
</div>

<div class="card mb-3">
    <div class="card-header">Scheduled Reports</div>
    <div class="card-body">
        <form method="POST" action="{{ route('reports.schedules.store') }}" class="form-row">@csrf
            <div class="col-md-3 mb-2"><input name="report_type" class="form-control" placeholder="report type" required></div>
            <div class="col-md-3 mb-2"><select name="schedule_frequency" class="form-control" required><option value="daily">Daily</option><option value="weekly">Weekly</option><option value="monthly">Monthly</option></select></div>
            <div class="col-md-3 mb-2"><select name="branch_id" class="form-control"><option value="">All branches</option>@foreach($branches as $branch)<option value="{{ $branch->id }}">{{ $branch->branch_name ?? $branch->name }}</option>@endforeach</select></div>
            <div class="col-md-3 mb-2"><button class="btn btn-success btn-block">Save Schedule</button></div>
        </form>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">Export History</div>
    <div class="card-body table-responsive p-0">
        <table class="table table-sm mb-0">
            <thead><tr><th>When</th><th>User</th><th>Report</th><th>Format</th><th>Branch</th><th>Status</th></tr></thead>
            <tbody>
                @forelse($exports as $item)
                    <tr>
                        <td>{{ $item->generated_at?->format('Y-m-d H:i') }}</td>
                        <td>{{ $item->user?->display_name }}</td>
                        <td>{{ ucfirst($item->report_type) }}</td>
                        <td>{{ strtoupper($item->export_format) }}</td>
                        <td>{{ $item->branch?->branch_name ?? $item->branch?->name ?? 'All' }}</td>
                        <td>{{ ucfirst($item->status) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted">No exports yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $exports->links() }}</div>
</div>
@endsection
