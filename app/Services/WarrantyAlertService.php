<?php

namespace App\Services;

use App\Models\Warranty;
use App\Models\WarrantyClaim;

class WarrantyAlertService
{
    public function __construct(private readonly NotificationService $notificationService)
    {
    }

    public function refreshAlerts(): void
    {
        $nearExpiry = Warranty::query()
            ->where('warranty_status', 'active')
            ->whereDate('warranty_end_date', '<=', now()->addDays(7)->toDateString())
            ->whereDate('warranty_end_date', '>=', now()->toDateString())
            ->get();

        foreach ($nearExpiry as $record) {
            $this->notificationService->create($record->customer_id, $record->branch_id, 'Warranty near expiration', 'Warranty '.$record->warranty_number.' is nearing expiration.', 'warranty', ['warranty_id' => $record->id]);
        }

        $expired = Warranty::query()
            ->where('warranty_status', 'active')
            ->whereDate('warranty_end_date', '<', now()->toDateString())
            ->get();

        foreach ($expired as $record) {
            $record->update(['warranty_status' => 'expired']);
            $this->notificationService->create($record->customer_id, $record->branch_id, 'Warranty expired', 'Warranty '.$record->warranty_number.' has expired.', 'warranty', ['warranty_id' => $record->id]);
        }

        WarrantyClaim::query()
            ->whereIn('claim_status', ['pending', 'under_review', 'under_repair'])
            ->whereDate('created_at', '<=', now()->subDays(7)->toDateString())
            ->get()
            ->each(function (WarrantyClaim $claim): void {
                $this->notificationService->create(null, $claim->branch_id, 'Claim overdue', 'Warranty claim '.$claim->claim_number.' is overdue.', 'warranty', ['claim_id' => $claim->id]);
            });
    }
}
