<?php

namespace App\Services;

use App\Models\BranchInventory;
use App\Models\InventoryMovement;
use App\Models\InventoryTransfer;

class InventoryAnalyticsService
{
    public function summary(array $filters = []): array
    {
        $inventory = BranchInventory::query();
        $inventory->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId));

        $movements = InventoryMovement::query();
        $movements->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId));
        $movements->when($filters['date_from'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '>=', $date));
        $movements->when($filters['date_to'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '<=', $date));

        return [
            'cards' => [
                'inventory_value' => (float) (clone $inventory)->sum('inventory_value'),
                'low_stock' => (int) (clone $inventory)->whereColumn('quantity_available', '<=', 'reorder_level')->count(),
                'dead_stock' => (int) (clone $inventory)->where('quantity_available', '>', 0)->where('last_stock_in_at', '<=', now()->subDays(120))->count(),
                'pending_transfers' => (int) InventoryTransfer::query()->whereIn('status', ['pending_approval', 'approved', 'in_transit'])->count(),
            ],
            'charts' => [
                'inventory_value_per_branch' => (clone $inventory)->selectRaw('branch_id as label, SUM(inventory_value) as value')->groupBy('branch_id')->get(),
                'inventory_movement_trend' => (clone $movements)->selectRaw('DATE(created_at) as label, SUM(quantity_in - quantity_out) as value')->groupByRaw('DATE(created_at)')->orderBy('label')->limit(31)->get(),
                'low_stock_trend' => (clone $movements)->selectRaw('DATE(created_at) as label, SUM(CASE WHEN quantity_out > 0 THEN 1 ELSE 0 END) as value')->groupByRaw('DATE(created_at)')->orderBy('label')->limit(31)->get(),
                'fast_moving_products' => (clone $movements)->selectRaw('product_id as label, SUM(quantity_out) as value')->groupBy('product_id')->orderByDesc('value')->limit(10)->get(),
                'slow_moving_products' => (clone $movements)->selectRaw('product_id as label, SUM(quantity_out) as value')->groupBy('product_id')->orderBy('value')->limit(10)->get(),
            ],
            'tables' => [
                'low_stock_products' => (clone $inventory)->with(['branch', 'product'])->whereColumn('quantity_available', '<=', 'reorder_level')->orderBy('quantity_available')->limit(15)->get(),
            ],
        ];
    }
}
