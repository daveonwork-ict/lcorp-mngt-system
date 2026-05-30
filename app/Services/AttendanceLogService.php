<?php

namespace App\Services;

use App\Models\AttendanceLog;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AttendanceLogService
{
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
        $this->ensureBranchAccess((int) $data['branch_id']);

        if (! empty($data['selfie_time_in'])) {
            $data['selfie_time_in_path'] = $data['selfie_time_in']->store('hr/attendance-selfies');
            unset($data['selfie_time_in']);
        }

        if (! empty($data['selfie_time_out'])) {
            $data['selfie_time_out_path'] = $data['selfie_time_out']->store('hr/attendance-selfies');
            unset($data['selfie_time_out']);
        }

        $data['device_info_in'] = ['raw' => (string) $data['device_info_in']];
        $data['device_info_out'] = ! empty($data['device_info_out']) ? ['raw' => (string) $data['device_info_out']] : null;
        $data['ip_address_in'] = request()->ip();
        $data['ip_address_out'] = request()->ip();
        $data = $this->attachCaptureMetadata($data);

        $log = AttendanceLog::query()->create($data);

        $this->auditLogService->record('hr_attendance', 'attendance_recorded', [], $log->toArray(), $log->branch_id, 'Attendance recorded');

        return $log;
    }

    public function update(AttendanceLog $attendanceLog, array $data): AttendanceLog
    {
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
            'captured_at' => $capturedAt ? (string) $capturedAt : null,
            'gps_latitude' => $latitude !== null ? (string) $latitude : null,
            'gps_longitude' => $longitude !== null ? (string) $longitude : null,
            'device_info_raw' => $deviceInfo,
            'ip_address' => $ipAddress,
            'file_path' => $filePath,
            'image_sha256' => $filePath ? $this->hashStoredFile($filePath) : null,
            'recorded_at' => now()->toIso8601String(),
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
