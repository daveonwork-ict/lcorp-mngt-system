<?php

namespace App\Services;

use App\Models\BranchInventory;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductImei;

class PurchasingInventoryIntegrationService
{
    public function receiveInventoryItem(int $branchId, int $productId, int $quantity, string $referenceType, int $referenceId, array $serializedEntries = []): void
    {
        $product = Product::query()->findOrFail($productId);

        $inventory = BranchInventory::query()->firstOrCreate(
            ['branch_id' => $branchId, 'product_id' => $productId],
            [
                'quantity_on_hand' => 0,
                'quantity_reserved' => 0,
                'quantity_available' => 0,
                'average_cost' => $product->cost_price,
                'inventory_value' => 0,
                'reorder_level' => $product->reorder_level,
            ]
        );

        $inventory->quantity_on_hand += $quantity;
        $inventory->quantity_available += $quantity;
        $inventory->inventory_value = (float) $inventory->quantity_on_hand * (float) $inventory->average_cost;
        $inventory->save();

        InventoryMovement::query()->create([
            'branch_id' => $branchId,
            'product_id' => $productId,
            'movement_type' => 'purchase_receipt',
            'quantity_in' => $quantity,
            'quantity_out' => 0,
            'running_balance' => $inventory->quantity_on_hand,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'remarks' => 'Inventory received from purchasing.',
            'performed_by' => auth()->id(),
        ]);

        if ($product->is_imei_required || $product->is_serialized) {
            foreach ($serializedEntries as $entry) {
                if (! ($entry['imei_number'] ?? null)) {
                    continue;
                }

                ProductImei::query()->firstOrCreate(
                    ['imei_number' => $entry['imei_number']],
                    [
                        'product_id' => $productId,
                        'branch_id' => $branchId,
                        'serial_number' => $entry['serial_number'] ?? null,
                        'status' => 'available',
                        'received_date' => now()->toDateString(),
                        'current_reference_type' => $referenceType,
                        'current_reference_id' => $referenceId,
                    ]
                );
            }
        }
    }
}
