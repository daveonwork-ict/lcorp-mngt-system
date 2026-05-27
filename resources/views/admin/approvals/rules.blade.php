@extends('layouts.app')

@section('page_title', 'Approval Rules')
@section('content')
<div class="card mb-3">
    <div class="card-header">Create Rule</div>
    <div class="card-body">
        <form method="POST" action="{{ route('approvals.rules.store') }}" class="form-row">@csrf
            <div class="col-md-3 mb-2"><input class="form-control" name="rule_name" placeholder="Rule name" required></div>
            <div class="col-md-2 mb-2"><input class="form-control" name="module_name" placeholder="Module" required></div>
            <div class="col-md-2 mb-2"><input class="form-control" name="transaction_type" placeholder="Type"></div>
            <div class="col-md-2 mb-2"><select class="form-control" name="branch_id"><option value="">All branches</option>@foreach($branches as $branch)<option value="{{ $branch->id }}">{{ $branch->branch_name ?? $branch->name }}</option>@endforeach</select></div>
            <div class="col-md-1 mb-2"><input type="number" min="1" class="form-control" name="approval_level" placeholder="Lvl" required></div>
            <div class="col-md-2 mb-2"><select class="form-control" name="approver_role_id" required><option value="">Approver role</option>@foreach($roles as $role)<option value="{{ $role->id }}">{{ $role->name }}</option>@endforeach</select></div>
            <div class="col-md-2 mb-2"><input type="number" min="0" step="0.01" class="form-control" name="minimum_amount" placeholder="Min"></div>
            <div class="col-md-2 mb-2"><input type="number" min="0" step="0.01" class="form-control" name="maximum_amount" placeholder="Max"></div>
            <div class="col-md-2 mb-2"><select class="form-control" name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
            <div class="col-md-2 mb-2"><button class="btn btn-success btn-block">Save Rule</button></div>
        </form>
    </div>
</div>

<div class="card"><div class="table-responsive p-0"><table class="table table-sm mb-0"><thead><tr><th>Name</th><th>Module</th><th>Branch</th><th>Level</th><th>Approver Role</th><th>Threshold</th><th>Status</th></tr></thead><tbody>@forelse($rules as $rule)<tr><td>{{ $rule->rule_name }}</td><td>{{ $rule->module_name }}</td><td>{{ $rule->branch?->branch_name ?? $rule->branch?->name ?? 'All' }}</td><td>{{ $rule->approval_level }}</td><td>{{ $rule->approverRole?->name }}</td><td>{{ $rule->minimum_amount }} - {{ $rule->maximum_amount }}</td><td>{{ ucfirst($rule->status) }}</td></tr>@empty<tr><td colspan="7" class="text-center text-muted">No rules yet.</td></tr>@endforelse</tbody></table></div><div class="card-footer">{{ $rules->links() }}</div></div>
@endsection
