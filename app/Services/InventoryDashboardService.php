<?php

namespace App\Services;

use App\Models\BranchInventory;
use App\Models\InventoryAdjustment;
use App\Models\InventoryTransfer;
use App\Models\Product;
use App\Models\ProductImei;

class InventoryDashboardService
{
    public function summary(?int $branchId = null): array
    {
        $inventoryQuery = BranchInventory::query()->when($branchId, fn ($q) => $q->where('branch_id', $branchId));

        $totalProducts = Product::query()->count();
        $totalValue = (float) (clone $inventoryQuery)->sum('inventory_value');
        $lowStock = (clone $inventoryQuery)->whereColumn('quantity_available', '<=', 'reorder_level')->count();
        $outOfStock = (clone $inventoryQuery)->where('quantity_available', '<=', 0)->count();
        $pendingTransfers = InventoryTransfer::query()->whereIn('status', ['pending_approval', 'approved', 'in_transit'])->count();
        $pendingAdjustments = InventoryAdjustment::query()->where('status', 'pending')->count();
        $serializedItems = Product::query()->where('is_serialized', true)->count();
        $defectiveItems = ProductImei::query()->where('status', 'defective')->count();

        return [
            'cards' => [
                ['label' => 'Total Products', 'value' => $totalProducts],
                ['label' => 'Total Inventory Value', 'value' => number_format($totalValue, 2)],
                ['label' => 'Low Stock Items', 'value' => $lowStock],
                ['label' => 'Out-of-Stock Items', 'value' => $outOfStock],
                ['label' => 'Pending Transfers', 'value' => $pendingTransfers],
                ['label' => 'Pending Adjustments', 'value' => $pendingAdjustments],
                ['label' => 'Serialized Items', 'value' => $serializedItems],
                ['label' => 'Defective Items', 'value' => $defectiveItems],
            ],
            'charts' => [
                'inventory_value_per_branch' => BranchInventory::query()->selectRaw('branch_id, SUM(inventory_value) as total_value')->groupBy('branch_id')->get(),
                'product_count_per_category' => Product::query()->selectRaw('category_id, COUNT(*) as total_products')->groupBy('category_id')->get(),
                'low_stock_by_branch' => BranchInventory::query()->selectRaw('branch_id, COUNT(*) as low_stock_count')->whereColumn('quantity_available', '<=', 'reorder_level')->groupBy('branch_id')->get(),
                'inventory_movement_trend' => \App\Models\InventoryMovement::query()->selectRaw('DATE(created_at) as movement_date, SUM(quantity_in) as total_in, SUM(quantity_out) as total_out')->groupByRaw('DATE(created_at)')->orderBy('movement_date')->get(),
            ],
        ];
    }
}
