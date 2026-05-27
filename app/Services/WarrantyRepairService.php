<?php

namespace App\Services;

use App\Models\WarrantyClaim;
use App\Models\WarrantyRepair;

class WarrantyRepairService
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function upsert(WarrantyClaim $claim, array $payload): WarrantyRepair
    {
        $repair = WarrantyRepair::query()->updateOrCreate(
            ['claim_id' => $claim->id],
            [
                'repair_details' => $payload['repair_details'] ?? null,
                'technician_name' => $payload['technician_name'] ?? null,
                'repair_start_date' => $payload['repair_start_date'] ?? null,
                'repair_end_date' => $payload['repair_end_date'] ?? null,
                'repair_status' => $payload['repair_status'] ?? 'under_repair',
                'remarks' => $payload['remarks'] ?? null,
            ]
        );

        $claim->update(['claim_status' => $repair->repair_status === 'completed' ? 'ready_for_release' : 'under_repair']);

        $this->auditLogService->record('warranty', 'warranty_repair_updated', [], $repair->toArray(), $claim->branch_id, 'Warranty repair tracking updated');

        return $repair;
    }
}
