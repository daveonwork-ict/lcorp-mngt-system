<?php

namespace App\Services;

use App\Models\StaffAccountability;

class StaffAccountabilityService
{
    public function paginate(array $filters = [])
    {
        return StaffAccountability::query()
            ->with(['employee', 'branch', 'supply', 'issuanceItem'])
            ->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->when($filters['employee_id'] ?? null, fn ($q, $employeeId) => $q->where('employee_id', $employeeId))
            ->when($filters['date_from'] ?? null, fn ($q, $date) => $q->whereDate('date_issued', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($q, $date) => $q->whereDate('date_issued', '<=', $date))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
    }
}
