<?php

namespace App\Services;

use App\Models\BranchInventory;
use App\Models\InventoryMovement;

class InventoryMovementService
{
    public function record(
        int $branchId,
        int $productId,
        string $movementType,
        int $quantityIn,
        int $quantityOut,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $remarks = null,
        ?int $imeiId = null
    ): InventoryMovement {
        $inventory = BranchInventory::query()->firstOrCreate(
            ['branch_id' => $branchId, 'product_id' => $productId],
            ['quantity_on_hand' => 0, 'quantity_reserved' => 0, 'quantity_available' => 0, 'average_cost' => 0, 'inventory_value' => 0]
        );

        $runningBalance = $inventory->quantity_on_hand + $quantityIn - $quantityOut;

        return InventoryMovement::query()->create([
            'branch_id' => $branchId,
            'product_id' => $productId,
            'imei_id' => $imeiId,
            'movement_type' => $movementType,
            'quantity_in' => $quantityIn,
            'quantity_out' => $quantityOut,
            'running_balance' => $runningBalance,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'remarks' => $remarks,
            'performed_by' => auth()->id(),
        ]);
    }
}
