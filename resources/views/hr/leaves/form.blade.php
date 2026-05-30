@extends('layouts.app')

@section('page_title', $mode === 'create' ? 'Create Leave Request' : 'Edit Leave Request')
@section('content')
<form method="POST" action="{{ $mode === 'create' ? route('hr.leaves.store') : route('hr.leaves.update', $leaveRequest) }}">
    @csrf
    @if ($mode === 'edit') @method('PUT') @endif
    <div class="card">
        <div class="card-body">
            <div class="form-row">
                <div class="col-md-4 mb-3"><label>Employee *</label><select name="user_id" class="form-control" required>@foreach($users as $user)<option value="{{ $user->id }}" @selected((int) old('user_id', $leaveRequest->user_id) === $user->id)>{{ $user->display_name }}</option>@endforeach</select></div>
                <div class="col-md-4 mb-3"><label>Branch *</label><select name="branch_id" class="form-control" required>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected((int) old('branch_id', $leaveRequest->branch_id) === $branch->id)>{{ $branch->branch_name ?? $branch->name }}</option>@endforeach</select></div>
                <div class="col-md-4 mb-3"><label>Leave Type *</label><select name="leave_type" class="form-control" required>@foreach(['vacation','sick','emergency','maternity','paternity','bereavement','without_pay'] as $type)<option value="{{ $type }}" @selected(old('leave_type', $leaveRequest->leave_type ?: 'vacation') === $type)>{{ ucfirst(str_replace('_',' ', $type)) }}</option>@endforeach</select></div>
            </div>
            <div class="form-row">
                <div class="col-md-3 mb-3"><label>Start Date *</label><input type="date" name="start_date" class="form-control" value="{{ old('start_date', optional($leaveRequest->start_date)->format('Y-m-d')) }}" required></div>
                <div class="col-md-3 mb-3"><label>End Date *</label><input type="date" name="end_date" class="form-control" value="{{ old('end_date', optional($leaveRequest->end_date)->format('Y-m-d')) }}" required></div>
                @if($mode === 'edit')
                <div class="col-md-3 mb-3"><label>Status</label><select name="status" class="form-control">@foreach(['pending_manager','pending_hr','approved','rejected','cancelled'] as $status)<option value="{{ $status }}" @selected(old('status', $leaveRequest->status) === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>@endforeach</select></div>
                <div class="col-md-3 mb-3"><label>Total Days</label><input class="form-control" value="{{ number_format((float)$leaveRequest->total_days,2) }}" disabled></div>
                @endif
            </div>
            <div class="form-row">
                <div class="col-md-12 mb-3"><label>Reason *</label><textarea name="reason" rows="3" class="form-control" required>{{ old('reason', $leaveRequest->reason) }}</textarea></div>
                @if($mode === 'edit')
                <div class="col-md-12 mb-3"><label>Final Remarks</label><textarea name="final_remarks" rows="2" class="form-control">{{ old('final_remarks', $leaveRequest->final_remarks) }}</textarea></div>
                @endif
            </div>
        </div>
        <div class="card-footer text-right">
            <a href="{{ route('hr.leaves.index') }}" class="btn btn-default">Cancel</a>
            <button class="btn btn-primary">Save Leave Request</button>
        </div>
    </div>
</form>
@endsection
