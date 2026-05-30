<?php

namespace App\Services;

use App\Models\AttendanceLog;
use Illuminate\Pagination\LengthAwarePaginator;
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
}
