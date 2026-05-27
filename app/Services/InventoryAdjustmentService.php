<?php

namespace App\Services;

use App\Models\BranchInventory;
use App\Models\InventoryAdjustment;
use Illuminate\Support\Facades\DB;

class InventoryAdjustmentService
{
    public function __construct(
        private readonly InventoryService $inventoryService,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    public function create(array $payload): InventoryAdjustment
    {
        $adjustment = InventoryAdjustment::query()->create([
            'adjustment_number' => $payload['adjustment_number'],
            'branch_id' => $payload['branch_id'],
            'reason' => $payload['reason'],
            'remarks' => $payload['remarks'] ?? null,
            'status' => $payload['status'] ?? 'pending',
            'requested_by' => auth()->id(),
        ]);

        foreach ($payload['items'] as $item) {
            $inventory = BranchInventory::query()->where('branch_id', $payload['branch_id'])->where('product_id', $item['product_id'])->first();
            $beforeQty = (int) ($inventory?->quantity_on_hand ?? 0);
            $afterQty = (int) $item['quantity_after'];

            $adjustment->items()->create([
                'product_id' => $item['product_id'],
                'quantity_before' => $beforeQty,
                'quantity_after' => $afterQty,
                'variance' => $afterQty - $beforeQty,
                'remarks' => $item['remarks'] ?? null,
            ]);
        }

        $this->auditLogService->record('inventory_adjustment', 'adjustment_created', [], $adjustment->toArray(), $adjustment->branch_id, 'Adjustment request created');

        return $adjustment->load('items');
    }

    public function approve(InventoryAdjustment $adjustment): InventoryAdjustment
    {
        return DB::transaction(function () use ($adjustment): InventoryAdjustment {
            foreach ($adjustment->items as $item) {
                $delta = $item->quantity_after - $item->quantity_before;
                if ($delta !== 0) {
                    $this->inventoryService->adjustStock(
                        $adjustment->branch_id,
                        $item->product_id,
                        $delta,
                        0,
                        'stock_adjustment',
                        'inventory_adjustment',
                        $adjustment->id,
                        'Adjustment approved'
                    );
                }
            }

            $adjustment->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            $this->auditLogService->record('inventory_adjustment', 'adjustment_approved', [], $adjustment->toArray(), $adjustment->branch_id, 'Adjustment approved');

            return $adjustment->fresh('items');
        });
    }
}
