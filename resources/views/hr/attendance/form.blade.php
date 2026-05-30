@extends('layouts.app')

@section('page_title', $mode === 'create' ? 'Record Attendance' : 'Edit Attendance')
@section('content')
@php
    $needsDeviceAssist = $mode === 'create'
        || ! $attendanceLog->selfie_time_in_path
        || is_null($attendanceLog->gps_latitude_in)
        || is_null($attendanceLog->gps_longitude_in)
        || ! data_get($attendanceLog->device_info_in, 'raw');
    $selfService = $selfService ?? false;
    $currentUser = auth()->user();
    $selectedBranch = $branches->firstWhere('id', old('branch_id', $attendanceLog->branch_id ?: $currentUser?->primary_branch_id));
@endphp
<form method="POST" action="{{ $mode === 'create' ? route('hr.attendance.store') : route('hr.attendance.update', $attendanceLog) }}" enctype="multipart/form-data">
    @csrf
    @if ($mode === 'edit') @method('PUT') @endif
    <div class="card">
        <div class="card-body">
            <div class="form-row">
                <div class="col-md-4 mb-3">
                    <label>Employee *</label>
                    @if ($selfService)
                        <input type="hidden" name="user_id" value="{{ old('user_id', $attendanceLog->user_id ?: $currentUser?->id) }}">
                        <input class="form-control" value="{{ $currentUser?->display_name }}" disabled>
                    @else
                        <select name="user_id" class="form-control" required>@foreach($users as $user)<option value="{{ $user->id }}" @selected((int) old('user_id', $attendanceLog->user_id) === $user->id)>{{ $user->display_name }}</option>@endforeach</select>
                    @endif
                </div>
                <div class="col-md-4 mb-3">
                    <label>Branch *</label>
                    @if ($selfService)
                        <input type="hidden" name="branch_id" value="{{ old('branch_id', $attendanceLog->branch_id ?: $currentUser?->primary_branch_id) }}">
                        <input class="form-control" value="{{ $selectedBranch?->branch_name ?? $selectedBranch?->name }}" disabled>
                    @else
                        <select name="branch_id" class="form-control" required>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected((int) old('branch_id', $attendanceLog->branch_id) === $branch->id)>{{ $branch->branch_name ?? $branch->name }}</option>@endforeach</select>
                    @endif
                </div>
                <div class="col-md-4 mb-3"><label>Date *</label><input type="date" name="attendance_date" class="form-control" value="{{ old('attendance_date', optional($attendanceLog->attendance_date)->format('Y-m-d') ?: now()->toDateString()) }}" required></div>
            </div>
            <div class="form-row">
                <div class="col-md-3 mb-3"><label>Schedule</label><select name="schedule_id" class="form-control"><option value="">None</option>@foreach($schedules as $schedule)<option value="{{ $schedule->id }}" @selected((int) old('schedule_id', $attendanceLog->schedule_id) === $schedule->id)>{{ optional($schedule->schedule_date)->format('Y-m-d') }} - {{ $schedule->user?->display_name }}</option>@endforeach</select></div>
                <div class="col-md-3 mb-3"><label>Time In *</label><input type="datetime-local" name="time_in" class="form-control" value="{{ old('time_in', optional($attendanceLog->time_in)->format('Y-m-d\TH:i')) }}" required></div>
                <div class="col-md-3 mb-3"><label>Time Out</label><input type="datetime-local" name="time_out" class="form-control" value="{{ old('time_out', optional($attendanceLog->time_out)->format('Y-m-d\TH:i')) }}"></div>
                <div class="col-md-3 mb-3"><label>Status *</label><select name="attendance_status" class="form-control" required>@foreach(['present','late','absent','undertime','overtime','leave','holiday'] as $status)<option value="{{ $status }}" @selected(old('attendance_status', $attendanceLog->attendance_status ?: 'present') === $status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
            </div>
            <div class="form-row">
                <div class="col-md-3 mb-3"><label>Selfie Time-In {{ $mode === 'create' ? '*' : '' }}</label><input id="selfie_time_in" type="file" name="selfie_time_in" accept="image/*" capture="user" class="form-control-file" {{ $mode === 'create' ? 'required' : '' }}></div>
                <div class="col-md-3 mb-3"><label>Selfie Time-Out</label><input id="selfie_time_out" type="file" name="selfie_time_out" accept="image/*" capture="user" class="form-control-file"></div>
                <div class="col-md-3 mb-3"><label>Device Info In *</label><input name="device_info_in" class="form-control" value="{{ old('device_info_in', data_get($attendanceLog->device_info_in, 'raw')) }}" required></div>
                <div class="col-md-3 mb-3"><label>Device Info Out</label><input name="device_info_out" class="form-control" value="{{ old('device_info_out', data_get($attendanceLog->device_info_out, 'raw')) }}"></div>
            </div>

            @if ($needsDeviceAssist)
                <div class="card card-outline card-info mb-3">
                    <div class="card-header"><strong>Device Capture</strong></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-6 mb-3">
                                <video id="attendance-camera" class="w-100 border rounded" autoplay playsinline muted style="max-height: 260px; background: #000;"></video>
                                <canvas id="attendance-canvas" class="d-none"></canvas>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-2">
                                    <button type="button" id="open-camera-btn" class="btn btn-outline-primary btn-sm">Open Camera</button>
                                    <button type="button" id="capture-in-btn" class="btn btn-primary btn-sm">Capture Time-In</button>
                                    <button type="button" id="capture-out-btn" class="btn btn-secondary btn-sm">Capture Time-Out</button>
                                </div>
                                <p id="camera-status" class="text-muted mb-2">Camera will auto-start if the browser allows it.</p>
                                <div class="row">
                                    <div class="col-6">
                                        <small class="text-muted d-block mb-1">Time-In Preview</small>
                                        <img id="preview-in" alt="Time-in preview" class="img-fluid border rounded d-none" style="max-height: 130px;">
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block mb-1">Time-Out Preview</small>
                                        <img id="preview-out" alt="Time-out preview" class="img-fluid border rounded d-none" style="max-height: 130px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="form-row">
                <div class="col-md-3 mb-3"><label>GPS Lat In</label><input id="gps_latitude_in" type="number" step="0.0000001" name="gps_latitude_in" class="form-control" value="{{ old('gps_latitude_in', $attendanceLog->gps_latitude_in) }}" {{ $mode === 'create' ? 'readonly' : '' }}></div>
                <div class="col-md-3 mb-3"><label>GPS Lng In</label><input id="gps_longitude_in" type="number" step="0.0000001" name="gps_longitude_in" class="form-control" value="{{ old('gps_longitude_in', $attendanceLog->gps_longitude_in) }}" {{ $mode === 'create' ? 'readonly' : '' }}></div>
                <div class="col-md-3 mb-3"><label>GPS Lat Out</label><input id="gps_latitude_out" type="number" step="0.0000001" name="gps_latitude_out" class="form-control" value="{{ old('gps_latitude_out', $attendanceLog->gps_latitude_out) }}" {{ $mode === 'create' ? 'readonly' : '' }}></div>
                <div class="col-md-3 mb-3"><label>GPS Lng Out</label><input id="gps_longitude_out" type="number" step="0.0000001" name="gps_longitude_out" class="form-control" value="{{ old('gps_longitude_out', $attendanceLog->gps_longitude_out) }}" {{ $mode === 'create' ? 'readonly' : '' }}></div>
            </div>
            @if ($needsDeviceAssist)
                <div class="mb-3">
                    <button type="button" id="refresh-gps-btn" class="btn btn-outline-info btn-sm">Refresh GPS</button>
                    <small id="gps-status" class="text-muted ml-2">Getting current location...</small>
                </div>
            @endif
        </div>
        <div class="card-footer text-right">
            <a href="{{ route('hr.attendance.index') }}" class="btn btn-default">Cancel</a>
            <button class="btn btn-primary">Save Attendance</button>
        </div>
    </div>
</form>
@endsection

@if ($needsDeviceAssist)
    @push('scripts')
        <script>
        (function () {
            const deviceIn = document.querySelector('input[name="device_info_in"]');
            const deviceOut = document.querySelector('input[name="device_info_out"]');

            if (deviceIn && !deviceIn.value) {
                deviceIn.value = navigator.userAgent;
            }

            if (deviceOut && !deviceOut.value) {
                deviceOut.value = navigator.userAgent;
            }

            const latIn = document.getElementById('gps_latitude_in');
            const lngIn = document.getElementById('gps_longitude_in');
            const latOut = document.getElementById('gps_latitude_out');
            const lngOut = document.getElementById('gps_longitude_out');
            const gpsStatus = document.getElementById('gps-status');
            const refreshGpsBtn = document.getElementById('refresh-gps-btn');
            const userSelect = document.querySelector('select[name="user_id"]');
            const branchSelect = document.querySelector('select[name="branch_id"]');

            const setGps = function () {
                return new Promise(function (resolve) {
                if (!navigator.geolocation) {
                    if (gpsStatus) gpsStatus.textContent = 'Geolocation is not supported on this device.';
                    resolve(null);
                    return;
                }

                if (gpsStatus) gpsStatus.textContent = 'Detecting location...';

                navigator.geolocation.getCurrentPosition(function (pos) {
                    const lat = pos.coords.latitude.toFixed(7);
                    const lng = pos.coords.longitude.toFixed(7);

                    if (latIn) latIn.value = lat;
                    if (lngIn) lngIn.value = lng;
                    if (latOut && !latOut.value) latOut.value = lat;
                    if (lngOut && !lngOut.value) lngOut.value = lng;
                    if (gpsStatus) gpsStatus.textContent = 'GPS updated automatically.';
                    resolve({ lat: lat, lng: lng });
                }, function (err) {
                    if (gpsStatus) gpsStatus.textContent = 'GPS unavailable: ' + err.message;
                    resolve(null);
                }, {
                    enableHighAccuracy: true,
                    timeout: 12000,
                    maximumAge: 0,
                });
                });
            };

            if (refreshGpsBtn) {
                refreshGpsBtn.addEventListener('click', setGps);
            }

            setGps();

            const video = document.getElementById('attendance-camera');
            const canvas = document.getElementById('attendance-canvas');
            const status = document.getElementById('camera-status');
            const inInput = document.getElementById('selfie_time_in');
            const outInput = document.getElementById('selfie_time_out');
            const inPreview = document.getElementById('preview-in');
            const outPreview = document.getElementById('preview-out');
            const openCameraBtn = document.getElementById('open-camera-btn');
            const captureInBtn = document.getElementById('capture-in-btn');
            const captureOutBtn = document.getElementById('capture-out-btn');

            let stream = null;

            const setFile = function (input, blob, filename) {
                const file = new File([blob], filename, { type: blob.type || 'image/jpeg' });
                const transfer = new DataTransfer();
                transfer.items.add(file);
                input.files = transfer.files;
            };

            const drawWatermark = function (ctx, width, height, captureLabel, gpsLat, gpsLng) {
                const branchName = branchSelect && branchSelect.selectedOptions.length
                    ? branchSelect.selectedOptions[0].text.trim()
                    : 'Unknown Branch';
                const employeeName = userSelect && userSelect.selectedOptions.length
                    ? userSelect.selectedOptions[0].text.trim()
                    : 'Unknown Employee';
                const timestamp = new Date().toLocaleString();
                const gpsText = (gpsLat && gpsLng)
                    ? (gpsLat + ', ' + gpsLng)
                    : 'GPS unavailable';

                const lines = [
                    'Attendance ' + captureLabel,
                    'Branch: ' + branchName,
                    'Name: ' + employeeName,
                    'Time: ' + timestamp,
                    'GPS: ' + gpsText,
                ];

                const baseFont = Math.max(13, Math.round(width * 0.02));
                const lineHeight = Math.round(baseFont * 1.35);
                ctx.font = '600 ' + baseFont + 'px Segoe UI, Arial, sans-serif';

                let maxWidth = 0;
                lines.forEach(function (line) {
                    maxWidth = Math.max(maxWidth, Math.ceil(ctx.measureText(line).width));
                });

                const paddingX = 14;
                const paddingY = 10;
                const boxWidth = maxWidth + (paddingX * 2);
                const boxHeight = (lineHeight * lines.length) + (paddingY * 2);
                const x = 16;
                const y = height - boxHeight - 16;

                ctx.fillStyle = 'rgba(0, 0, 0, 0.55)';
                ctx.fillRect(x, y, boxWidth, boxHeight);

                ctx.strokeStyle = 'rgba(255, 255, 255, 0.35)';
                ctx.lineWidth = 1;
                ctx.strokeRect(x + 0.5, y + 0.5, boxWidth - 1, boxHeight - 1);

                ctx.fillStyle = '#ffffff';
                lines.forEach(function (line, index) {
                    ctx.fillText(line, x + paddingX, y + paddingY + lineHeight * (index + 0.8));
                });
            };

            const resolveGpsForCapture = async function (captureType) {
                const fresh = await setGps();
                if (fresh && fresh.lat && fresh.lng) {
                    return fresh;
                }

                const useOut = captureType === 'out';
                const latValue = (useOut ? (latOut && latOut.value) : (latIn && latIn.value)) || (latIn && latIn.value) || '';
                const lngValue = (useOut ? (lngOut && lngOut.value) : (lngIn && lngIn.value)) || (lngIn && lngIn.value) || '';

                return {
                    lat: latValue ? String(latValue) : '',
                    lng: lngValue ? String(lngValue) : '',
                };
            };

            const capture = async function (targetInput, targetPreview, filename, captureType) {
                if (!video || !canvas || !targetInput || !video.videoWidth || !video.videoHeight) {
                    if (status) status.textContent = 'Camera is not ready yet.';
                    return;
                }

                if (status) status.textContent = 'Capturing and stamping watermark...';

                const gps = await resolveGpsForCapture(captureType);

                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;

                const ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                drawWatermark(ctx, canvas.width, canvas.height, captureType === 'out' ? 'Time-Out' : 'Time-In', gps.lat, gps.lng);

                canvas.toBlob(function (blob) {
                    if (!blob) return;

                    setFile(targetInput, blob, filename);
                    if (targetPreview) {
                        targetPreview.src = URL.createObjectURL(blob);
                        targetPreview.classList.remove('d-none');
                    }

                    if (status) status.textContent = 'Photo captured successfully.';
                }, 'image/jpeg', 0.9);
            };

            const startCamera = async function () {
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    if (status) status.textContent = 'Camera API is not supported on this browser.';
                    return;
                }

                try {
                    stream = await navigator.mediaDevices.getUserMedia({
                        video: { facingMode: 'user' },
                        audio: false,
                    });

                    video.srcObject = stream;
                    if (status) status.textContent = 'Camera ready. Capture your selfie.';
                } catch (error) {
                    if (status) status.textContent = 'Camera access denied or unavailable.';
                }
            };

            if (openCameraBtn) openCameraBtn.addEventListener('click', startCamera);
            if (captureInBtn) captureInBtn.addEventListener('click', function () { capture(inInput, inPreview, 'attendance-time-in.jpg', 'in'); });
            if (captureOutBtn) captureOutBtn.addEventListener('click', function () { capture(outInput, outPreview, 'attendance-time-out.jpg', 'out'); });

            startCamera();
        })();
        </script>
    @endpush
@endif
