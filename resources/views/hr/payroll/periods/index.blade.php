@extends('layouts.app')

@section('page_title', 'Payroll Periods')
@section('content')
<div class="card mb-3">
    <div class="card-header">Create Payroll Period</div>
    <div class="card-body">
        <form method="POST" action="{{ route('hr.payroll.periods.store') }}" class="form-row">@csrf
            <div class="col-md-2 mb-2"><input name="period_code" class="form-control" placeholder="Period Code" required></div>
            <div class="col-md-2 mb-2"><select name="payroll_period_type" class="form-control" required><option value="weekly">Weekly</option><option value="semi_monthly" selected>Semi-Monthly</option><option value="monthly">Monthly</option></select></div>
            <div class="col-md-2 mb-2"><input type="date" name="period_start" class="form-control" required></div>
            <div class="col-md-2 mb-2"><input type="date" name="period_end" class="form-control" required></div>
            <div class="col-md-2 mb-2"><select name="status" class="form-control" required><option value="draft">Draft</option><option value="pending_approval">Pending Approval</option><option value="approved">Approved</option><option value="released">Released</option><option value="cancelled">Cancelled</option></select></div>
            <div class="col-md-2 mb-2"><button class="btn btn-primary btn-block">Save</button></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">Payroll Period List</div>
    <div class="table-responsive p-0">
        <table class="table table-hover mb-0">
            <thead><tr><th>Code</th><th>Type</th><th>Start</th><th>End</th><th>Status</th></tr></thead>
            <tbody>
            @forelse($periods as $period)
                <tr>
                    <td>{{ $period->period_code }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $period->payroll_period_type)) }}</td>
                    <td>{{ optional($period->period_start)->format('Y-m-d') }}</td>
                    <td>{{ optional($period->period_end)->format('Y-m-d') }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $period->status)) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted">No payroll periods found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $periods->links() }}</div>
</div>
@endsection
