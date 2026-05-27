<?php

namespace App\Services;

use App\Models\BranchInventory;
use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

class InventoryService
{
    public function __construct(
        private readonly InventoryMovementService $movementService,
        private readonly InventoryAlertService $alertService,
        private readonly AuditLogService $auditLogService,
        private readonly BranchAccessService $branchAccessService,
    ) {
    }

    public function branchInventory(array $filters = []): LengthAwarePaginator
    {
        return BranchInventory::query()
            ->with(['branch', 'product.category', 'product.brand'])
            ->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->when($filters['category_id'] ?? null, fn ($q, $categoryId) => $q->whereHas('product', fn ($sub) => $sub->where('category_id', $categoryId)))
            ->when($filters['brand_id'] ?? null, fn ($q, $brandId) => $q->whereHas('product', fn ($sub) => $sub->where('brand_id', $brandId)))
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->whereHas('product', fn ($sub) => $sub->where('product_name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%")))
            ->paginate(20)
            ->withQueryString();
    }

    public function adjustStock(int $branchId, int $productId, int $deltaQty, float $costPrice = 0, ?string $movementType = null, ?string $referenceType = null, ?int $referenceId = null, ?string $remarks = null): BranchInventory
    {
        $inventory = BranchInventory::query()->firstOrCreate(
            ['branch_id' => $branchId, 'product_id' => $productId],
            ['quantity_on_hand' => 0, 'quantity_reserved' => 0, 'quantity_available' => 0, 'average_cost' => 0, 'inventory_value' => 0]
        );

        $before = $inventory->toArray();

        $newOnHand = $inventory->quantity_on_hand + $deltaQty;
        if ($newOnHand < 0) {
            $this->alertService->createAlert($branchId, $productId, 'negative_stock_attempt', 'critical', 'Negative stock attempt blocked.');
            abort(422, 'Negative stock is not allowed.');
        }

        $inventory->quantity_on_hand = $newOnHand;
        $inventory->quantity_available = $newOnHand - $inventory->quantity_reserved;

        if ($deltaQty > 0 && $costPrice > 0) {
            $totalCost = ($inventory->average_cost * max(0, $inventory->quantity_on_hand - $deltaQty)) + ($costPrice * $deltaQty);
            $inventory->average_cost = $inventory->quantity_on_hand > 0 ? round($totalCost / $inventory->quantity_on_hand, 2) : $inventory->average_cost;
        }

        $inventory->reorder_level = $inventory->reorder_level ?: (int) (Product::query()->find($productId)?->reorder_level ?? 0);
        $inventory->inventory_value = round($inventory->average_cost * $inventory->quantity_on_hand, 2);
        $inventory->save();

        $this->movementService->record(
            $branchId,
            $productId,
            $movementType ?? ($deltaQty >= 0 ? 'stock_in' : 'stock_out'),
            max(0, $deltaQty),
            max(0, abs(min(0, $deltaQty))),
            $referenceType,
            $referenceId,
            $remarks
        );

        $this->auditLogService->record('inventory', 'inventory_changed', $before, $inventory->toArray(), $branchId, 'Inventory quantity changed');
        $this->alertService->refreshLowStockAlerts($branchId);

        return $inventory;
    }

    public function validateBranchAccess(int $branchId): void
    {
        $user = auth()->user();
        if (! $user) {
            abort(401);
        }

        if (! $this->branchAccessService->canAccessBranch($user, $branchId)) {
            abort(403, 'Branch access denied.');
        }
    }
}
