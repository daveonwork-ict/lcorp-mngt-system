<?php

namespace App\Services;

use App\Models\AttendanceLog;
use App\Models\Branch;
use App\Models\EmployeeSchedule;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AttendanceLogService
{
    private const ATTENDANCE_TIMEZONE = 'Asia/Manila';

    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly BranchAccessService $branchAccessService,
    ) {
    }

    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $user = Auth::user();

        return AttendanceLog::query()
            ->with(['user', 'branch', 'schedule'])
            ->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->when($filters['user_id'] ?? null, fn ($q, $userId) => $q->where('user_id', $userId))
            ->when($filters['date_from'] ?? null, fn ($q, $date) => $q->whereDate('attendance_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($q, $date) => $q->whereDate('attendance_date', '<=', $date))
            ->when($user && ! in_array($user->role?->code, [config('rms.owner_role_code'), 'super_admin', 'branch_manager'], true), fn ($q) => $q->where('user_id', $user->id))
            ->when($user && in_array($user->role?->code, ['branch_manager'], true), fn ($q) => $q->whereIn('branch_id', $this->branchAccessService->accessibleBranches($user)->pluck('id')->all()))
            ->orderByDesc('attendance_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();
    }

    public function verifyCaptureMetadata(AttendanceLog $attendanceLog, string $captureType): array
    {
        $metadata = $captureType === 'out'
            ? $attendanceLog->capture_metadata_out
            : $attendanceLog->capture_metadata_in;

        $filePath = $captureType === 'out'
            ? $attendanceLog->selfie_time_out_path
            : $attendanceLog->selfie_time_in_path;

        if (! is_array($metadata) || empty($metadata['payload']) || empty($metadata['signature'])) {
            return [
                'status' => 'missing',
                'label' => 'Missing Verification',
                'details' => 'No signed capture metadata stored for this image.',
            ];
        }

        $payload = $metadata['payload'];
        $expectedSignature = hash_hmac('sha256', json_encode($payload, JSON_UNESCAPED_SLASHES), $this->signatureKey());
        if (! hash_equals((string) $metadata['signature'], $expectedSignature)) {
            return [
                'status' => 'invalid',
                'label' => 'Signature Mismatch',
                'details' => 'Stored capture signature does not match the recorded payload.',
            ];
        }

        $storedHash = Arr::get($payload, 'image_sha256');
        $currentHash = $filePath && Storage::exists($filePath)
            ? hash('sha256', (string) Storage::get($filePath))
            : null;

        if ($storedHash && $currentHash && ! hash_equals($storedHash, $currentHash)) {
            return [
                'status' => 'invalid',
                'label' => 'File Hash Mismatch',
                'details' => 'The stored selfie file no longer matches the original recorded hash.',
            ];
        }

        if (! $filePath || ! Storage::exists($filePath)) {
            return [
                'status' => 'warning',
                'label' => 'File Missing',
                'details' => 'Verification metadata exists, but the selfie file is not currently available.',
            ];
        }

        return [
            'status' => 'valid',
            'label' => 'Verified',
            'details' => 'Signature and stored file hash both match the recorded capture metadata.',
        ];
    }

    public function create(array $data): AttendanceLog
    {
        $data = $this->resolveOwnedRecordData($data);
        $data = $this->normalizeAttendanceTimes($data);
        $this->ensureBranchAccess((int) $data['branch_id']);

        if ($this->isSelfServiceContext()) {
            return $this->recordSelfServiceAttendance($data);
        }

        if (! empty($data['selfie_time_in'])) {
            $data['selfie_time_in_path'] = $data['selfie_time_in']->store('hr/attendance-selfies');
            unset($data['selfie_time_in']);
        }

        if (! empty($data['selfie_time_out'])) {
            $data['selfie_time_out_path'] = $data['selfie_time_out']->store('hr/attendance-selfies');
            unset($data['selfie_time_out']);
        }

        $schedule = $this->resolveSchedule($data);
        if ($schedule && empty($data['schedule_id'])) {
            $data['schedule_id'] = $schedule->id;
        }

        $data['device_info_in'] = ['raw' => (string) $data['device_info_in']];
        $data['device_info_out'] = ! empty($data['device_info_out']) ? ['raw' => (string) $data['device_info_out']] : null;
        $data['ip_address_in'] = request()->ip();
        $data['ip_address_out'] = request()->ip();
        $data = $this->applyScheduleMetrics($data, $schedule);
        $data = $this->attachCaptureMetadata($data);

        $log = AttendanceLog::query()->create($data);

        $this->auditLogService->record('hr_attendance', 'attendance_recorded', [], $log->toArray(), $log->branch_id, 'Attendance recorded');

        return $log;
    }

    public function update(AttendanceLog $attendanceLog, array $data): AttendanceLog
    {
        $data = $this->resolveOwnedRecordData($data, $attendanceLog);
        $data = $this->normalizeAttendanceTimes($data);
        $this->ensureBranchAccess((int) $data['branch_id']);

        $before = $attendanceLog->toArray();

        if (! empty($data['selfie_time_in'])) {
            if ($attendanceLog->selfie_time_in_path) {
                Storage::delete($attendanceLog->selfie_time_in_path);
            }

            $data['selfie_time_in_path'] = $data['selfie_time_in']->store('hr/attendance-selfies');
            unset($data['selfie_time_in']);
        }

        if (! empty($data['selfie_time_out'])) {
            if ($attendanceLog->selfie_time_out_path) {
                Storage::delete($attendanceLog->selfie_time_out_path);
            }

            $data['selfie_time_out_path'] = $data['selfie_time_out']->store('hr/attendance-selfies');
            unset($data['selfie_time_out']);
        }

        $data['device_info_in'] = ['raw' => (string) $data['device_info_in']];
        $data['device_info_out'] = ! empty($data['device_info_out']) ? ['raw' => (string) $data['device_info_out']] : null;
        $data['ip_address_in'] = $attendanceLog->ip_address_in ?: request()->ip();
        $data['ip_address_out'] = request()->ip();
        $data['selfie_time_in_path'] = $data['selfie_time_in_path'] ?? $attendanceLog->selfie_time_in_path;
        $data['selfie_time_out_path'] = $data['selfie_time_out_path'] ?? $attendanceLog->selfie_time_out_path;
        $schedule = $this->resolveSchedule($data);
        if ($schedule && empty($data['schedule_id'])) {
            $data['schedule_id'] = $schedule->id;
        }
        $data = $this->applyScheduleMetrics($data, $schedule);
        $data = $this->attachCaptureMetadata($data);

        $attendanceLog->update($data);

        $this->auditLogService->record('hr_attendance', 'attendance_updated', $before, $attendanceLog->toArray(), $attendanceLog->branch_id, 'Attendance updated');

        return $attendanceLog;
    }

    private function ensureBranchAccess(int $branchId): void
    {
        $user = Auth::user();

        if ($user && ! $this->branchAccessService->canAccessBranch($user, $branchId)) {
            abort(403, 'Branch attendance access denied.');
        }
    }

    private function resolveOwnedRecordData(array $data, ?AttendanceLog $attendanceLog = null): array
    {
        $user = Auth::user();

        if ($user && ! in_array($user->role?->code, [config('rms.owner_role_code'), 'super_admin', 'branch_manager'], true)) {
            $data['user_id'] = $user->id;
            $data['branch_id'] = $attendanceLog?->branch_id ?? $user->primary_branch_id ?? $data['branch_id'];
        }

        return $data;
    }

    private function isSelfServiceContext(): bool
    {
        $user = Auth::user();

        return (bool) $user && ! in_array($user->role?->code, [config('rms.owner_role_code'), 'super_admin', 'branch_manager'], true);
    }

    private function resolveSchedule(array $data): ?EmployeeSchedule
    {
        if (! empty($data['schedule_id'])) {
            return EmployeeSchedule::query()->find($data['schedule_id']);
        }

        if (empty($data['user_id']) || empty($data['attendance_date'])) {
            return null;
        }

        return EmployeeSchedule::query()
            ->where('user_id', $data['user_id'])
            ->whereDate('schedule_date', (string) $data['attendance_date'])
            ->latest('id')
            ->first();
    }

    private function applyScheduleMetrics(array $data, ?EmployeeSchedule $schedule): array
    {
        if (! $schedule || $schedule->is_rest_day) {
            return $data;
        }

        $attendanceDate = (string) ($data['attendance_date'] ?? optional($schedule->schedule_date)->toDateString());
        $lateMinutes = 0;
        $undertimeMinutes = 0;
        $overtimeMinutes = 0;

        if (! empty($data['time_in']) && $schedule->time_in) {
            $actualTimeIn = ($data['time_in'] instanceof Carbon
                ? $data['time_in']->copy()
                : Carbon::parse((string) $data['time_in']))->timezone(self::ATTENDANCE_TIMEZONE);
            $scheduledTimeIn = Carbon::parse($attendanceDate.' '.$schedule->time_in, self::ATTENDANCE_TIMEZONE);
            $lateMinutes = max($scheduledTimeIn->diffInMinutes($actualTimeIn, false), 0);
        }

        if (! empty($data['time_out']) && $schedule->time_out) {
            $actualTimeOut = ($data['time_out'] instanceof Carbon
                ? $data['time_out']->copy()
                : Carbon::parse((string) $data['time_out']))->timezone(self::ATTENDANCE_TIMEZONE);
            $scheduledTimeOut = Carbon::parse($attendanceDate.' '.$schedule->time_out, self::ATTENDANCE_TIMEZONE);
            $delta = $scheduledTimeOut->diffInMinutes($actualTimeOut, false);

            if ($delta < 0) {
                $undertimeMinutes = abs($delta);
            } elseif ($delta > 0) {
                $overtimeMinutes = $delta;
            }
        }

        $status = 'present';
        if ($undertimeMinutes > 0) {
            $status = 'undertime';
        } elseif ($overtimeMinutes > 0) {
            $status = 'overtime';
        } elseif ($lateMinutes > 0) {
            $status = 'late';
        }

        $data['late_minutes'] = $lateMinutes;
        $data['undertime_minutes'] = $undertimeMinutes;
        $data['overtime_minutes'] = $overtimeMinutes;
        $data['attendance_status'] = $status;

        return $data;
    }

    private function recordSelfServiceAttendance(array $data): AttendanceLog
    {
        $todayDate = Carbon::now(self::ATTENDANCE_TIMEZONE)->toDateString();
        $data['attendance_date'] = $todayDate;

        $schedule = $this->resolveSchedule($data);
        if (! $schedule) {
            throw ValidationException::withMessages([
                'schedule_id' => 'No plotted schedule for today. Attendance is not allowed.',
            ]);
        }

        if ($schedule->is_rest_day) {
            throw ValidationException::withMessages([
                'schedule_id' => 'Today is marked as rest day. Attendance check-in is not allowed.',
            ]);
        }

        $data['schedule_id'] = $schedule->id;

        $todayLog = AttendanceLog::query()
            ->where('user_id', $data['user_id'])
            ->whereDate('attendance_date', $todayDate)
            ->latest('id')
            ->first();

        if ($todayLog && $todayLog->time_in && $todayLog->time_out) {
            throw ValidationException::withMessages([
                'time_out' => 'Today\'s attendance is already completed.',
            ]);
        }

        if (! $todayLog) {
            if (empty($data['selfie_time_in'])) {
                throw ValidationException::withMessages([
                    'selfie_time_in' => 'Clock-in selfie capture is required.',
                ]);
            }

            $data['time_in'] = $data['time_in'] ?? Carbon::now(self::ATTENDANCE_TIMEZONE)->utc();
            $data['time_out'] = null;
            $data['selfie_time_in_path'] = $data['selfie_time_in']->store('hr/attendance-selfies');
            unset($data['selfie_time_in'], $data['selfie_time_out']);

            $data['device_info_in'] = ['raw' => (string) $data['device_info_in']];
            $data['device_info_out'] = null;
            $data['ip_address_in'] = request()->ip();
            $data['ip_address_out'] = null;
            $data = $this->applyScheduleMetrics($data, $schedule);
            $data = $this->attachCaptureMetadata($data);

            $log = AttendanceLog::query()->create($data);
            $this->auditLogService->record('hr_attendance', 'attendance_clocked_in', [], $log->toArray(), $log->branch_id, 'Attendance clock-in recorded');

            return $log;
        }

        if (empty($data['selfie_time_out'])) {
            throw ValidationException::withMessages([
                'selfie_time_out' => 'Clock-out selfie capture is required.',
            ]);
        }

        $before = $todayLog->toArray();
        $clockOutAt = $data['time_out'] ?? Carbon::now(self::ATTENDANCE_TIMEZONE)->utc();
        $selfieOutPath = $data['selfie_time_out']->store('hr/attendance-selfies');

        $metadataData = [
            'user_id' => $todayLog->user_id,
            'branch_id' => $todayLog->branch_id,
            'attendance_date' => $todayLog->attendance_date?->toDateString() ?? $todayDate,
            'schedule_id' => $schedule->id,
            'time_in' => $todayLog->time_in,
            'time_out' => $clockOutAt,
            'selfie_time_in_path' => $todayLog->selfie_time_in_path,
            'selfie_time_out_path' => $selfieOutPath,
            'gps_latitude_in' => $todayLog->gps_latitude_in,
            'gps_longitude_in' => $todayLog->gps_longitude_in,
            'gps_latitude_out' => $data['gps_latitude_out'] ?? $todayLog->gps_latitude_out,
            'gps_longitude_out' => $data['gps_longitude_out'] ?? $todayLog->gps_longitude_out,
            'device_info_in' => $todayLog->device_info_in,
            'device_info_out' => ['raw' => (string) ($data['device_info_out'] ?? $data['device_info_in'])],
            'ip_address_in' => $todayLog->ip_address_in,
            'ip_address_out' => request()->ip(),
            'late_minutes' => $todayLog->late_minutes,
            'undertime_minutes' => $todayLog->undertime_minutes,
            'overtime_minutes' => $todayLog->overtime_minutes,
            'attendance_status' => $todayLog->attendance_status,
        ];

        $metadataData = $this->applyScheduleMetrics($metadataData, $schedule);
        $metadataData = $this->attachCaptureMetadata($metadataData);

        $todayLog->update([
            'schedule_id' => $schedule->id,
            'time_out' => $clockOutAt,
            'selfie_time_out_path' => $selfieOutPath,
            'gps_latitude_out' => $metadataData['gps_latitude_out'] ?? null,
            'gps_longitude_out' => $metadataData['gps_longitude_out'] ?? null,
            'device_info_out' => $metadataData['device_info_out'],
            'ip_address_out' => request()->ip(),
            'late_minutes' => $metadataData['late_minutes'],
            'undertime_minutes' => $metadataData['undertime_minutes'],
            'overtime_minutes' => $metadataData['overtime_minutes'],
            'attendance_status' => $metadataData['attendance_status'],
            'capture_metadata_in' => $metadataData['capture_metadata_in'],
            'capture_metadata_out' => $metadataData['capture_metadata_out'],
        ]);

        $this->auditLogService->record('hr_attendance', 'attendance_clocked_out', $before, $todayLog->toArray(), $todayLog->branch_id, 'Attendance clock-out recorded');

        return $todayLog;
    }

    private function normalizeAttendanceTimes(array $data): array
    {
        if (! empty($data['attendance_date'])) {
            $data['attendance_date'] = Carbon::parse((string) $data['attendance_date'], self::ATTENDANCE_TIMEZONE)->toDateString();
        }

        if (! empty($data['time_in'])) {
            $timeInManila = Carbon::parse((string) $data['time_in'], self::ATTENDANCE_TIMEZONE);
            $data['time_in'] = $timeInManila->copy()->utc();
            $data['attendance_date'] = $data['attendance_date'] ?? $timeInManila->toDateString();
        }

        if (! empty($data['time_out'])) {
            $data['time_out'] = Carbon::parse((string) $data['time_out'], self::ATTENDANCE_TIMEZONE)->utc();
        }

        return $data;
    }

    private function attachCaptureMetadata(array $data): array
    {
        $branchName = Branch::query()->whereKey($data['branch_id'])->value('branch_name')
            ?? Branch::query()->whereKey($data['branch_id'])->value('name');
        $userName = User::query()->whereKey($data['user_id'])->value('full_name')
            ?? User::query()->whereKey($data['user_id'])->value('name');

        $data['capture_metadata_in'] = $this->buildCaptureMetadata(
            'in',
            $data,
            $branchName,
            $userName,
            $data['selfie_time_in_path'] ?? null,
            $data['time_in'] ?? null,
            $data['gps_latitude_in'] ?? null,
            $data['gps_longitude_in'] ?? null,
            $data['device_info_in']['raw'] ?? null,
            $data['ip_address_in'] ?? null,
        );

        $data['capture_metadata_out'] = $this->buildCaptureMetadata(
            'out',
            $data,
            $branchName,
            $userName,
            $data['selfie_time_out_path'] ?? null,
            $data['time_out'] ?? null,
            $data['gps_latitude_out'] ?? null,
            $data['gps_longitude_out'] ?? null,
            $data['device_info_out']['raw'] ?? null,
            $data['ip_address_out'] ?? null,
        );

        return $data;
    }

    private function buildCaptureMetadata(
        string $captureType,
        array $data,
        ?string $branchName,
        ?string $userName,
        ?string $filePath,
        mixed $capturedAt,
        mixed $latitude,
        mixed $longitude,
        ?string $deviceInfo,
        ?string $ipAddress,
    ): ?array {
        if (! $filePath && ! $capturedAt && ! $latitude && ! $longitude && ! $deviceInfo) {
            return null;
        }

        $payload = [
            'capture_type' => $captureType,
            'user_id' => (int) $data['user_id'],
            'user_name' => $userName,
            'branch_id' => (int) $data['branch_id'],
            'branch_name' => $branchName,
            'attendance_date' => (string) $data['attendance_date'],
            'captured_at' => $capturedAt ? Carbon::parse((string) $capturedAt)->timezone(self::ATTENDANCE_TIMEZONE)->toIso8601String() : null,
            'gps_latitude' => $latitude !== null ? (string) $latitude : null,
            'gps_longitude' => $longitude !== null ? (string) $longitude : null,
            'device_info_raw' => $deviceInfo,
            'ip_address' => $ipAddress,
            'file_path' => $filePath,
            'image_sha256' => $filePath ? $this->hashStoredFile($filePath) : null,
            'recorded_at' => now()->timezone(self::ATTENDANCE_TIMEZONE)->toIso8601String(),
        ];

        return [
            'algorithm' => 'hmac-sha256',
            'signature' => hash_hmac('sha256', json_encode($payload, JSON_UNESCAPED_SLASHES), $this->signatureKey()),
            'payload' => $payload,
        ];
    }

    private function hashStoredFile(string $filePath): ?string
    {
        if (! Storage::exists($filePath)) {
            return null;
        }

        return hash('sha256', (string) Storage::get($filePath));
    }

    private function signatureKey(): string
    {
        $key = (string) config('app.key', 'attendance-capture-key');

        return str_starts_with($key, 'base64:')
            ? (string) base64_decode(substr($key, 7), true)
            : $key;
    }
}
