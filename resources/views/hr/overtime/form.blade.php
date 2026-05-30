@extends('layouts.app')

@section('page_title', $mode === 'create' ? 'Create Overtime Request' : 'Edit Overtime Request')
@section('content')
@php
    $selfService = $selfService ?? false;
    $currentUser = auth()->user();
    $selectedBranch = $branches->firstWhere('id', old('branch_id', $overtimeRequest->branch_id ?: $currentUser?->primary_branch_id));
@endphp
<form method="POST" action="{{ $mode === 'create' ? route('hr.overtime.store') : route('hr.overtime.update', $overtimeRequest) }}">
    @csrf
    @if ($mode === 'edit') @method('PUT') @endif
    <div class="card">
        <div class="card-body">
            <div class="form-row">
                <div class="col-md-4 mb-3">
                    <label>Employee *</label>
                    @if ($selfService)
                        <input type="hidden" name="user_id" value="{{ old('user_id', $overtimeRequest->user_id ?: $currentUser?->id) }}">
                        <input class="form-control" value="{{ $currentUser?->display_name }}" disabled>
                    @else
                        <select name="user_id" class="form-control" required>@foreach($users as $user)<option value="{{ $user->id }}" @selected((int) old('user_id', $overtimeRequest->user_id) === $user->id)>{{ $user->display_name }}</option>@endforeach</select>
                    @endif
                </div>
                <div class="col-md-4 mb-3">
                    <label>Branch *</label>
                    @if ($selfService)
                        <input type="hidden" name="branch_id" value="{{ old('branch_id', $overtimeRequest->branch_id ?: $currentUser?->primary_branch_id) }}">
                        <input class="form-control" value="{{ $selectedBranch?->branch_name ?? $selectedBranch?->name }}" disabled>
                    @else
                        <select name="branch_id" class="form-control" required>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected((int) old('branch_id', $overtimeRequest->branch_id) === $branch->id)>{{ $branch->branch_name ?? $branch->name }}</option>@endforeach</select>
                    @endif
                </div>
                <div class="col-md-4 mb-3"><label>Overtime Date *</label><input type="date" name="overtime_date" class="form-control" value="{{ old('overtime_date', optional($overtimeRequest->overtime_date)->format('Y-m-d')) }}" required></div>
            </div>
            <div class="form-row">
                <div class="col-md-4 mb-3"><label>Hours *</label><input type="number" min="0.5" max="24" step="0.25" name="hours" class="form-control" value="{{ old('hours', $overtimeRequest->hours ?: 1) }}" required></div>
                @if($mode === 'edit')
                <div class="col-md-4 mb-3"><label>Status</label><select name="status" class="form-control">@foreach(['pending_manager','pending_hr','approved','rejected','cancelled'] as $status)<option value="{{ $status }}" @selected(old('status', $overtimeRequest->status) === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>@endforeach</select></div>
                @endif
            </div>
            <div class="form-row">
                <div class="col-md-12 mb-3"><label>Reason *</label><textarea name="reason" rows="4" class="form-control" required>{{ old('reason', $overtimeRequest->reason) }}</textarea></div>
            </div>
        </div>
        <div class="card-footer text-right">
            <a href="{{ route('hr.overtime.index') }}" class="btn btn-default">Cancel</a>
            <button class="btn btn-primary">Save Overtime Request</button>
        </div>
    </div>
</form>
@endsection
