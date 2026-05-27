<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImei;
use App\Models\Sale;
use App\Models\SaleItem;

class SalesInventoryService
{
    public function __construct(
        private readonly InventoryService $inventoryService,
        private readonly InventoryMovementService $movementService,
        private readonly SalesAuditService $salesAuditService,
    ) {
    }

    public function assertSellable(int $branchId, Product $product, int $quantity, ?int $imeiId = null): void
    {
        $this->inventoryService->validateBranchAccess($branchId);

        $inventory = $product->inventories()->where('branch_id', $branchId)->first();
        $available = (int) ($inventory?->quantity_available ?? 0);

        if ($available < $quantity) {
            abort(422, 'Insufficient stock for '.$product->product_name.'.');
        }

        if ($product->is_imei_required || $product->is_serialized) {
            if (! $imeiId) {
                abort(422, 'IMEI selection is required for serialized products.');
            }

            $imei = ProductImei::query()->whereKey($imeiId)->where('product_id', $product->id)->first();
            if (! $imei) {
                abort(422, 'Selected IMEI not found for product.');
            }

            if ((int) $imei->branch_id !== $branchId) {
                abort(422, 'Selected IMEI does not belong to this branch.');
            }

            if ($imei->status !== 'available') {
                abort(422, 'Selected IMEI is not available.');
            }
        }
    }

    public function deductForSale(Sale $sale): void
    {
        $sale->load('items.product', 'items.imei');

        foreach ($sale->items as $item) {
            $this->inventoryService->adjustStock(
                $sale->branch_id,
                $item->product_id,
                -1 * (int) $item->quantity,
                0,
                'sale_deduction',
                'sale',
                $sale->id,
                'Deducted by POS sale '.$sale->sales_number
            );

            if ($item->imei_id) {
                $item->imei?->update([
                    'status' => 'sold',
                    'sold_date' => now(),
                    'current_reference_type' => 'sale_item',
                    'current_reference_id' => $item->id,
                ]);
            }

            $this->movementService->record(
                $sale->branch_id,
                $item->product_id,
                'sale_deduction',
                0,
                (int) $item->quantity,
                'sale_item',
                $item->id,
                'POS sale deduction',
                $item->imei_id
            );
        }

        $this->salesAuditService->log('inventory_deducted', [], ['sale_id' => $sale->id], $sale->branch_id, 'Inventory deducted for sale');
    }

    public function restoreForVoid(Sale $sale): void
    {
        $sale->load('items.imei');

        foreach ($sale->items as $item) {
            $this->inventoryService->adjustStock(
                $sale->branch_id,
                $item->product_id,
                (int) $item->quantity,
                (float) $item->cost_price,
                'sale_void_restore',
                'sale',
                $sale->id,
                'Restored by sale void '.$sale->sales_number
            );

            if ($item->imei_id) {
                $item->imei?->update([
                    'status' => 'available',
                    'current_reference_type' => 'sale_void',
                    'current_reference_id' => $sale->id,
                ]);
            }
        }

        $this->salesAuditService->log('inventory_restored', [], ['sale_id' => $sale->id], $sale->branch_id, 'Inventory restored from void');
    }

    public function restoreForReturn(SaleItem $saleItem, int $branchId, int $quantity): void
    {
        $this->inventoryService->adjustStock(
            $branchId,
            $saleItem->product_id,
            $quantity,
            (float) $saleItem->cost_price,
            'sale_return_restore',
            'sale_item',
            $saleItem->id,
            'Restored by sale return'
        );

        if ($saleItem->imei_id) {
            $saleItem->imei?->update([
                'status' => 'available',
                'current_reference_type' => 'sale_return',
                'current_reference_id' => $saleItem->id,
            ]);
        }
    }
}
