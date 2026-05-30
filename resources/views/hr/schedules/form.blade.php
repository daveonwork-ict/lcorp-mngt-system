@extends('layouts.app')

@section('page_title', $mode === 'create' ? 'Create Schedule' : 'Edit Schedule')
@section('content')
<form method="POST" action="{{ $mode === 'create' ? route('hr.schedules.store') : route('hr.schedules.update', $schedule) }}">
    @csrf
    @if ($mode === 'edit') @method('PUT') @endif
    <div class="card">
        <div class="card-body">
            <div class="form-row">
                <div class="col-md-4 mb-3"><label>Employee *</label><select name="user_id" class="form-control" required>@foreach($users as $user)<option value="{{ $user->id }}" @selected((int) old('user_id', $schedule->user_id) === $user->id)>{{ $user->display_name }}</option>@endforeach</select></div>
                <div class="col-md-4 mb-3"><label>Branch *</label><select name="branch_id" class="form-control" required>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected((int) old('branch_id', $schedule->branch_id) === $branch->id)>{{ $branch->branch_name ?? $branch->name }}</option>@endforeach</select></div>
                <div class="col-md-4 mb-3"><label>Schedule Date *</label><input type="date" name="schedule_date" class="form-control" value="{{ old('schedule_date', optional($schedule->schedule_date)->format('Y-m-d')) }}" required></div>
            </div>
            <div class="form-row">
                <div class="col-md-3 mb-3"><label>Schedule Type *</label><select name="schedule_type" class="form-control" required>@foreach(['fixed','rotating','flexible'] as $type)<option value="{{ $type }}" @selected(old('schedule_type', $schedule->schedule_type ?: 'fixed') === $type)>{{ ucfirst($type) }}</option>@endforeach</select></div>
                <div class="col-md-2 mb-3"><label>Time In</label><input type="time" name="time_in" class="form-control" value="{{ old('time_in', $schedule->time_in) }}"></div>
                <div class="col-md-2 mb-3"><label>Time Out</label><input type="time" name="time_out" class="form-control" value="{{ old('time_out', $schedule->time_out) }}"></div>
                <div class="col-md-2 mb-3"><label>Break Start</label><input type="time" name="break_start" class="form-control" value="{{ old('break_start', $schedule->break_start) }}"></div>
                <div class="col-md-2 mb-3"><label>Break End</label><input type="time" name="break_end" class="form-control" value="{{ old('break_end', $schedule->break_end) }}"></div>
                <div class="col-md-1 mb-3"><label>Rest</label><div><input type="checkbox" name="is_rest_day" value="1" @checked((bool) old('is_rest_day', $schedule->is_rest_day))></div></div>
            </div>
        </div>
        <div class="card-footer text-right">
            <a href="{{ route('hr.schedules.index') }}" class="btn btn-default">Cancel</a>
            <button class="btn btn-primary">Save Schedule</button>
        </div>
    </div>
</form>
@endsection
