@extends('layouts.app')

@section('page_title', 'Cash Advances')
@section('content')
<div class="card mb-3">
    <div class="card-header">Create Cash Advance Request</div>
    <div class="card-body">
        <form method="POST" action="{{ route('hr.cash-advances.store') }}" class="form-row">@csrf
            <div class="col-md-3 mb-2"><select name="user_id" class="form-control" required><option value="">Employee</option>@foreach($users as $user)<option value="{{ $user->id }}">{{ $user->display_name }}</option>@endforeach</select></div>
            <div class="col-md-3 mb-2"><select name="branch_id" class="form-control" required><option value="">Branch</option>@foreach($branches as $branch)<option value="{{ $branch->id }}">{{ $branch->branch_name ?? $branch->name }}</option>@endforeach</select></div>
            <div class="col-md-2 mb-2"><input type="number" step="0.01" min="1" name="amount" class="form-control" placeholder="Amount" required></div>
            <div class="col-md-2 mb-2"><input type="date" name="request_date" class="form-control" value="{{ now()->toDateString() }}" required></div>
            <div class="col-md-2 mb-2"><button class="btn btn-primary btn-block">Submit</button></div>
            <div class="col-md-12"><input name="reason" class="form-control" placeholder="Reason (optional)"></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">Cash Advance Records</div>
    <div class="table-responsive p-0">
        <table class="table table-hover mb-0">
            <thead><tr><th>Date</th><th>Employee</th><th>Branch</th><th>Amount</th><th>Remaining</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($records as $row)
                <tr>
                    <td>{{ optional($row->request_date)->format('Y-m-d') }}</td>
                    <td>{{ $row->user?->display_name }}</td>
                    <td>{{ $row->branch?->branch_name ?? $row->branch?->name }}</td>
                    <td>{{ number_format((float) $row->amount, 2) }}</td>
                    <td>{{ number_format((float) $row->remaining_balance, 2) }}</td>
                    <td>{{ ucfirst($row->status) }}</td>
                    <td>
                        @if($row->status === 'pending')
                        <form method="POST" action="{{ route('hr.cash-advances.approve', $row) }}" class="d-inline">@csrf<button class="btn btn-xs btn-outline-success">Approve</button></form>
                        @endif
                        @if($row->status === 'approved')
                        <form method="POST" action="{{ route('hr.cash-advances.release', $row) }}" class="d-inline">@csrf<button class="btn btn-xs btn-outline-primary">Release</button></form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted">No cash advance records.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $records->links() }}</div>
</div>
@endsection
