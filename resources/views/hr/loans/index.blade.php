@extends('layouts.app')

@section('page_title', 'Employee Loans')
@section('content')
<div class="card mb-3">
    <div class="card-header">Create Loan</div>
    <div class="card-body">
        <form method="POST" action="{{ route('hr.loans.store') }}" class="form-row">@csrf
            <div class="col-md-3 mb-2"><select name="user_id" class="form-control" required><option value="">Employee</option>@foreach($users as $user)<option value="{{ $user->id }}">{{ $user->display_name }}</option>@endforeach</select></div>
            <div class="col-md-3 mb-2"><select name="branch_id" class="form-control" required><option value="">Branch</option>@foreach($branches as $branch)<option value="{{ $branch->id }}">{{ $branch->branch_name ?? $branch->name }}</option>@endforeach</select></div>
            <div class="col-md-2 mb-2"><select name="loan_type" class="form-control" required><option value="company">Company</option><option value="salary">Salary</option><option value="emergency">Emergency</option><option value="sss">SSS</option><option value="pagibig">Pag-IBIG</option><option value="other">Other</option></select></div>
            <div class="col-md-2 mb-2"><input type="number" step="0.01" min="1" name="principal_amount" class="form-control" placeholder="Principal" required></div>
            <div class="col-md-2 mb-2"><input type="number" step="0.0001" min="0" name="interest_rate" class="form-control" placeholder="Interest" value="0"></div>
            <div class="col-md-2 mb-2"><input type="number" step="0.01" min="1" name="installment_amount" class="form-control" placeholder="Installment" required></div>
            <div class="col-md-2 mb-2"><input type="number" min="1" max="240" name="term_months" class="form-control" placeholder="Term" required></div>
            <div class="col-md-2 mb-2"><input type="date" name="start_date" class="form-control" required></div>
            <div class="col-md-2 mb-2"><input type="date" name="maturity_date" class="form-control"></div>
            <div class="col-md-2 mb-2"><button class="btn btn-primary btn-block">Create</button></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">Loan Records</div>
    <div class="table-responsive p-0">
        <table class="table table-hover mb-0">
            <thead><tr><th>Loan #</th><th>Employee</th><th>Branch</th><th>Principal</th><th>Remaining</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($loans as $loan)
                <tr>
                    <td>{{ $loan->loan_number }}</td>
                    <td>{{ $loan->user?->display_name }}</td>
                    <td>{{ $loan->branch?->branch_name ?? $loan->branch?->name }}</td>
                    <td>{{ number_format((float) $loan->principal_amount, 2) }}</td>
                    <td>{{ number_format((float) $loan->remaining_balance, 2) }}</td>
                    <td>{{ ucfirst($loan->status) }}</td>
                    <td>
                        @if($loan->status === 'pending')
                        <form method="POST" action="{{ route('hr.loans.approve', $loan) }}" class="d-inline">@csrf<button class="btn btn-xs btn-outline-success">Approve</button></form>
                        @endif
                        @if(in_array($loan->status, ['pending','active'], true) && !$loan->released_at)
                        <form method="POST" action="{{ route('hr.loans.release', $loan) }}" class="d-inline">@csrf<button class="btn btn-xs btn-outline-primary">Release</button></form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted">No loan records.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $loans->links() }}</div>
</div>
@endsection
