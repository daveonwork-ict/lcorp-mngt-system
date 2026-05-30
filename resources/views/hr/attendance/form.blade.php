@extends('layouts.app')

@section('page_title', $mode === 'create' ? 'Record Attendance' : 'Edit Attendance')
@section('content')
<form method="POST" action="{{ $mode === 'create' ? route('hr.attendance.store') : route('hr.attendance.update', $attendanceLog) }}" enctype="multipart/form-data">
    @csrf
    @if ($mode === 'edit') @method('PUT') @endif
    <div class="card">
        <div class="card-body">
            <div class="form-row">
                <div class="col-md-4 mb-3"><label>Employee *</label><select name="user_id" class="form-control" required>@foreach($users as $user)<option value="{{ $user->id }}" @selected((int) old('user_id', $attendanceLog->user_id) === $user->id)>{{ $user->display_name }}</option>@endforeach</select></div>
                <div class="col-md-4 mb-3"><label>Branch *</label><select name="branch_id" class="form-control" required>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected((int) old('branch_id', $attendanceLog->branch_id) === $branch->id)>{{ $branch->branch_name ?? $branch->name }}</option>@endforeach</select></div>
                <div class="col-md-4 mb-3"><label>Date *</label><input type="date" name="attendance_date" class="form-control" value="{{ old('attendance_date', optional($attendanceLog->attendance_date)->format('Y-m-d') ?: now()->toDateString()) }}" required></div>
            </div>
            <div class="form-row">
                <div class="col-md-3 mb-3"><label>Schedule</label><select name="schedule_id" class="form-control"><option value="">None</option>@foreach($schedules as $schedule)<option value="{{ $schedule->id }}" @selected((int) old('schedule_id', $attendanceLog->schedule_id) === $schedule->id)>{{ optional($schedule->schedule_date)->format('Y-m-d') }} - {{ $schedule->user?->display_name }}</option>@endforeach</select></div>
                <div class="col-md-3 mb-3"><label>Time In *</label><input type="datetime-local" name="time_in" class="form-control" value="{{ old('time_in', optional($attendanceLog->time_in)->format('Y-m-d\TH:i')) }}" required></div>
                <div class="col-md-3 mb-3"><label>Time Out</label><input type="datetime-local" name="time_out" class="form-control" value="{{ old('time_out', optional($attendanceLog->time_out)->format('Y-m-d\TH:i')) }}"></div>
                <div class="col-md-3 mb-3"><label>Status *</label><select name="attendance_status" class="form-control" required>@foreach(['present','late','absent','undertime','overtime','leave','holiday'] as $status)<option value="{{ $status }}" @selected(old('attendance_status', $attendanceLog->attendance_status ?: 'present') === $status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
            </div>
            <div class="form-row">
                <div class="col-md-3 mb-3"><label>Selfie Time-In {{ $mode === 'create' ? '*' : '' }}</label><input type="file" name="selfie_time_in" class="form-control-file" {{ $mode === 'create' ? 'required' : '' }}></div>
                <div class="col-md-3 mb-3"><label>Selfie Time-Out</label><input type="file" name="selfie_time_out" class="form-control-file"></div>
                <div class="col-md-3 mb-3"><label>Device Info In *</label><input name="device_info_in" class="form-control" value="{{ old('device_info_in', data_get($attendanceLog->device_info_in, 'raw')) }}" required></div>
                <div class="col-md-3 mb-3"><label>Device Info Out</label><input name="device_info_out" class="form-control" value="{{ old('device_info_out', data_get($attendanceLog->device_info_out, 'raw')) }}"></div>
            </div>
            <div class="form-row">
                <div class="col-md-3 mb-3"><label>GPS Lat In</label><input type="number" step="0.0000001" name="gps_latitude_in" class="form-control" value="{{ old('gps_latitude_in', $attendanceLog->gps_latitude_in) }}"></div>
                <div class="col-md-3 mb-3"><label>GPS Lng In</label><input type="number" step="0.0000001" name="gps_longitude_in" class="form-control" value="{{ old('gps_longitude_in', $attendanceLog->gps_longitude_in) }}"></div>
                <div class="col-md-3 mb-3"><label>GPS Lat Out</label><input type="number" step="0.0000001" name="gps_latitude_out" class="form-control" value="{{ old('gps_latitude_out', $attendanceLog->gps_latitude_out) }}"></div>
                <div class="col-md-3 mb-3"><label>GPS Lng Out</label><input type="number" step="0.0000001" name="gps_longitude_out" class="form-control" value="{{ old('gps_longitude_out', $attendanceLog->gps_longitude_out) }}"></div>
            </div>
        </div>
        <div class="card-footer text-right">
            <a href="{{ route('hr.attendance.index') }}" class="btn btn-default">Cancel</a>
            <button class="btn btn-primary">Save Attendance</button>
        </div>
    </div>
</form>
@endsection
