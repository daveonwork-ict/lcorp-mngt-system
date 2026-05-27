<?php

namespace App\Services;

use App\Models\WarrantyClaim;
use App\Models\WarrantyReplacement;

class WarrantyReplacementService
{
    public function __construct(
        private readonly NotificationService $notificationService,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    public function replace(WarrantyClaim $claim, array $payload): WarrantyReplacement
    {
        $replacement = WarrantyReplacement::query()->updateOrCreate(
            ['claim_id' => $claim->id],
            [
                'old_product_id' => $payload['old_product_id'],
                'old_imei_id' => $payload['old_imei_id'] ?? null,
                'replacement_product_id' => $payload['replacement_product_id'] ?? null,
                'replacement_imei_id' => $payload['replacement_imei_id'] ?? null,
                'replacement_date' => $payload['replacement_date'],
                'approved_by' => auth()->id(),
                'remarks' => $payload['remarks'] ?? null,
            ]
        );

        if ($replacement->oldImei) {
            $replacement->oldImei->update(['status' => 'replaced', 'current_reference_type' => 'warranty_replacement', 'current_reference_id' => $replacement->id]);
        }

        if ($replacement->replacementImei) {
            $replacement->replacementImei->update(['status' => 'sold', 'current_reference_type' => 'warranty_replacement', 'current_reference_id' => $replacement->id]);
        }

        $claim->update(['claim_status' => 'replaced', 'resolution_type' => 'replacement']);
        $claim->warranty?->update(['warranty_status' => 'replaced']);

        $this->notificationService->create($claim->customer_id, $claim->branch_id, 'Warranty replacement recorded', 'Claim '.$claim->claim_number.' has a replacement record.', 'warranty', ['claim_id' => $claim->id]);
        $this->auditLogService->record('warranty', 'warranty_replacement_recorded', [], $replacement->toArray(), $claim->branch_id, 'Warranty replacement recorded');

        return $replacement;
    }
}
