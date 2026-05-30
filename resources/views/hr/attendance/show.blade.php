@extends('layouts.app')

@section('page_title', 'Attendance Details')
@section('content')
@php
    $statusClasses = [
        'valid' => 'success',
        'warning' => 'warning',
        'invalid' => 'danger',
        'missing' => 'secondary',
    ];
@endphp

<div class="row">
    <div class="col-lg-5">
        <div class="card mb-3">
            <div class="card-header"><strong>Attendance Summary</strong></div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Employee</dt><dd class="col-sm-8">{{ $attendanceLog->user?->display_name }}</dd>
                    <dt class="col-sm-4">Branch</dt><dd class="col-sm-8">{{ $attendanceLog->branch?->branch_name ?? $attendanceLog->branch?->name }}</dd>
                    <dt class="col-sm-4">Date</dt><dd class="col-sm-8">{{ optional($attendanceLog->attendance_date)->format('Y-m-d') }}</dd>
                    <dt class="col-sm-4">Clock In (UTC+8)</dt><dd class="col-sm-8">{{ $attendanceLog->time_in ? $attendanceLog->time_in->timezone('Asia/Manila')->format('Y-m-d H:i:s') : '-' }}</dd>
                    <dt class="col-sm-4">Clock Out (UTC+8)</dt><dd class="col-sm-8">{{ $attendanceLog->time_out ? $attendanceLog->time_out->timezone('Asia/Manila')->format('Y-m-d H:i:s') : '-' }}</dd>
                    <dt class="col-sm-4">Status</dt><dd class="col-sm-8">{{ ucfirst($attendanceLog->attendance_status) }}</dd>
                    <dt class="col-sm-4">GPS In</dt><dd class="col-sm-8">{{ $attendanceLog->gps_latitude_in && $attendanceLog->gps_longitude_in ? $attendanceLog->gps_latitude_in.', '.$attendanceLog->gps_longitude_in : '-' }}</dd>
                    <dt class="col-sm-4">GPS Out</dt><dd class="col-sm-8">{{ $attendanceLog->gps_latitude_out && $attendanceLog->gps_longitude_out ? $attendanceLog->gps_latitude_out.', '.$attendanceLog->gps_longitude_out : '-' }}</dd>
                    <dt class="col-sm-4">Device In</dt><dd class="col-sm-8 text-break">{{ data_get($attendanceLog->device_info_in, 'raw', '-') }}</dd>
                    <dt class="col-sm-4">Device Out</dt><dd class="col-sm-8 text-break">{{ data_get($attendanceLog->device_info_out, 'raw', '-') }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        @foreach (['in' => $verificationIn, 'out' => $verificationOut] as $captureType => $verification)
            @php
                $label = $captureType === 'in' ? 'Clock-In' : 'Clock-Out';
                $path = $captureType === 'in' ? $attendanceLog->selfie_time_in_path : $attendanceLog->selfie_time_out_path;
                $metadata = $captureType === 'in' ? $attendanceLog->capture_metadata_in : $attendanceLog->capture_metadata_out;
            @endphp
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>{{ $label }} Capture</strong>
                    <span class="badge badge-{{ $statusClasses[$verification['status']] ?? 'secondary' }}">{{ $verification['label'] }}</span>
                </div>
                <div class="card-body">
                    @if ($path)
                        <div class="mb-3">
                            <img src="{{ route('hr.attendance.selfies.preview', ['attendance' => $attendanceLog, 'captureType' => $captureType]) }}" alt="{{ $label }} Selfie" class="img-fluid border rounded" style="max-height: 420px;">
                        </div>
                    @else
                        <p class="text-muted">No selfie captured for {{ strtolower($label) }}.</p>
                    @endif

                    <p class="mb-2"><strong>Verification:</strong> {{ $verification['details'] }}</p>

                    @if (is_array($metadata))
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <tbody>
                                    <tr><th style="width: 180px;">Algorithm</th><td>{{ $metadata['algorithm'] ?? '-' }}</td></tr>
                                    <tr><th>Signature</th><td class="text-break">{{ $metadata['signature'] ?? '-' }}</td></tr>
                                    <tr><th>Recorded At</th><td>{{ data_get($metadata, 'payload.recorded_at', '-') }}</td></tr>
                                    <tr><th>Captured At</th><td>{{ data_get($metadata, 'payload.captured_at', '-') }}</td></tr>
                                    <tr><th>Branch</th><td>{{ data_get($metadata, 'payload.branch_name', '-') }}</td></tr>
                                    <tr><th>Name</th><td>{{ data_get($metadata, 'payload.user_name', '-') }}</td></tr>
                                    <tr><th>GPS</th><td>{{ data_get($metadata, 'payload.gps_latitude') && data_get($metadata, 'payload.gps_longitude') ? data_get($metadata, 'payload.gps_latitude').', '.data_get($metadata, 'payload.gps_longitude') : '-' }}</td></tr>
                                    <tr><th>File SHA-256</th><td class="text-break">{{ data_get($metadata, 'payload.image_sha256', '-') }}</td></tr>
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">No capture metadata available.</p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>

<div class="text-right">
    <form method="POST" action="{{ route('hr.attendance.reverify', $attendanceLog) }}" class="d-inline">
        @csrf
        <button class="btn btn-outline-info">Reverify</button>
    </form>
    <a href="{{ route('hr.attendance.index') }}" class="btn btn-default">Back</a>
    <a href="{{ route('hr.attendance.edit', $attendanceLog) }}" class="btn btn-primary">Edit Attendance</a>
</div>
@endsection