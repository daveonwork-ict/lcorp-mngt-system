<?php

namespace App\Services;

use App\Models\BranchInventory;
use App\Models\InventoryAlert;
use App\Models\Notification;

class InventoryAlertService
{
    public function createAlert(?int $branchId, ?int $productId, string $type, string $severity, string $message): InventoryAlert
    {
        $alert = InventoryAlert::query()->create([
            'branch_id' => $branchId,
            'product_id' => $productId,
            'alert_type' => $type,
            'severity' => $severity,
            'message' => $message,
            'is_resolved' => false,
        ]);

        Notification::query()->create([
            'branch_id' => $branchId,
            'title' => 'Inventory Alert: '.strtoupper(str_replace('_', ' ', $type)),
            'message' => $message,
            'type' => 'inventory_alert',
            'is_read' => false,
            'payload' => ['alert_id' => $alert->id, 'product_id' => $productId],
        ]);

        return $alert;
    }

    public function refreshLowStockAlerts(?int $branchId = null): void
    {
        $inventories = BranchInventory::query()
            ->with('product')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->get();

        foreach ($inventories as $inventory) {
            if ($inventory->quantity_available <= 0) {
                $this->createAlert(
                    $inventory->branch_id,
                    $inventory->product_id,
                    'out_of_stock',
                    'high',
                    $inventory->product?->product_name.' is out of stock.'
                );
            } elseif ($inventory->quantity_available <= max(1, $inventory->reorder_level)) {
                $this->createAlert(
                    $inventory->branch_id,
                    $inventory->product_id,
                    'low_stock',
                    'medium',
                    $inventory->product?->product_name.' is below reorder level.'
                );
            }
        }
    }
}
