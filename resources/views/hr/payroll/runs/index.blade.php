@extends('layouts.app')

@section('page_title', 'Payroll Runs')
@section('content')
<div class="card mb-3">
    <div class="card-header">Generate Payroll Run</div>
    <div class="card-body">
        <form method="POST" action="{{ route('hr.payroll.runs.generate') }}" class="form-row">@csrf
            <div class="col-md-5 mb-2"><select name="payroll_period_id" class="form-control" required><option value="">Select Period</option>@foreach($periods as $period)<option value="{{ $period->id }}">{{ $period->period_code }} ({{ optional($period->period_start)->format('Y-m-d') }} to {{ optional($period->period_end)->format('Y-m-d') }})</option>@endforeach</select></div>
            <div class="col-md-5 mb-2"><select name="branch_id" class="form-control"><option value="">All Branches</option>@foreach($branches as $branch)<option value="{{ $branch->id }}">{{ $branch->branch_name ?? $branch->name }}</option>@endforeach</select></div>
            <div class="col-md-2 mb-2"><button class="btn btn-primary btn-block">Generate</button></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">Payroll Run List</div>
    <div class="table-responsive p-0">
        <table class="table table-hover mb-0">
            <thead><tr><th>ID</th><th>Period</th><th>Branch</th><th>Status</th><th>Gross</th><th>Deductions</th><th>Net</th><th></th></tr></thead>
            <tbody>
            @forelse($runs as $run)
                <tr>
                    <td>#{{ $run->id }}</td>
                    <td>{{ $run->period?->period_code }}</td>
                    <td>{{ $run->branch?->branch_name ?? $run->branch?->name ?? 'All Branches' }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $run->status)) }}</td>
                    <td>{{ number_format((float) $run->total_gross_pay, 2) }}</td>
                    <td>{{ number_format((float) $run->total_deductions, 2) }}</td>
                    <td>{{ number_format((float) $run->total_net_pay, 2) }}</td>
                    <td><a href="{{ route('hr.payroll.runs.show', $run) }}" class="btn btn-xs btn-outline-primary">Open</a></td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted">No payroll runs found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $runs->links() }}</div>
</div>
@endsection
