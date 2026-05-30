<?php

namespace App\Services;

use App\Models\EmployeeSchedule;
use Illuminate\Pagination\LengthAwarePaginator;

class EmployeeScheduleService
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return EmployeeSchedule::query()
            ->with(['user', 'branch'])
            ->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->when($filters['user_id'] ?? null, fn ($q, $userId) => $q->where('user_id', $userId))
            ->when($filters['date_from'] ?? null, fn ($q, $date) => $q->whereDate('schedule_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($q, $date) => $q->whereDate('schedule_date', '<=', $date))
            ->orderByDesc('schedule_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();
    }

    public function create(array $data): EmployeeSchedule
    {
        $schedule = EmployeeSchedule::query()->updateOrCreate(
            ['user_id' => $data['user_id'], 'schedule_date' => $data['schedule_date']],
            $data
        );

        $this->auditLogService->record('hr_schedules', 'schedule_saved', [], $schedule->toArray(), $schedule->branch_id, 'Employee schedule saved');

        return $schedule;
    }

    public function update(EmployeeSchedule $schedule, array $data): EmployeeSchedule
    {
        $before = $schedule->toArray();
        $schedule->update($data);

        $this->auditLogService->record('hr_schedules', 'schedule_updated', $before, $schedule->toArray(), $schedule->branch_id, 'Employee schedule updated');

        return $schedule;
    }
}
