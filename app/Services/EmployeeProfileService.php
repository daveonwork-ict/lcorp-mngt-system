<?php

namespace App\Services;

use App\Models\EmployeeProfile;
use Illuminate\Pagination\LengthAwarePaginator;

class EmployeeProfileService
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return EmployeeProfile::query()
            ->with(['user', 'branch', 'position'])
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->whereHas('user', function ($userQuery) use ($search): void {
                $userQuery->where('full_name', 'like', "%{$search}%")
                    ->orWhere('employee_code', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            }))
            ->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->when($filters['employment_status'] ?? null, fn ($q, $status) => $q->where('employment_status', $status))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();
    }

    public function create(array $data): EmployeeProfile
    {
        $profile = EmployeeProfile::query()->create($data);

        $this->auditLogService->record('hr_employees', 'employee_profile_created', [], $profile->toArray(), $profile->branch_id, 'Employee profile created');

        return $profile;
    }

    public function update(EmployeeProfile $profile, array $data): EmployeeProfile
    {
        $before = $profile->toArray();

        $profile->update($data);

        $this->auditLogService->record('hr_employees', 'employee_profile_updated', $before, $profile->toArray(), $profile->branch_id, 'Employee profile updated');

        return $profile;
    }
}
