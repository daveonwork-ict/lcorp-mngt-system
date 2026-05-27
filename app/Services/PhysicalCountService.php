<?php

namespace App\Services;

use App\Models\BranchInventory;
use App\Models\PhysicalCount;

class PhysicalCountService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly InventoryAdjustmentService $adjustmentService,
    ) {
    }

    public function create(array $payload): PhysicalCount
    {
        $count = PhysicalCount::query()->create([
            'count_number' => $payload['count_number'],
            'branch_id' => $payload['branch_id'],
            'category_id' => $payload['category_id'] ?? null,
            'status' => $payload['status'] ?? 'open',
            'created_by' => auth()->id(),
        ]);

        foreach ($payload['items'] as $item) {
            $inventory = BranchInventory::query()->where('branch_id', $payload['branch_id'])->where('product_id', $item['product_id'])->first();
            $systemQty = (int) ($inventory?->quantity_on_hand ?? 0);
            $countedQty = (int) $item['counted_quantity'];

            $count->items()->create([
                'product_id' => $item['product_id'],
                'system_quantity' => $systemQty,
                'counted_quantity' => $countedQty,
                'variance' => $countedQty - $systemQty,
                'encoded_imei' => $item['encoded_imei'] ?? null,
                'remarks' => $item['remarks'] ?? null,
            ]);
        }

        $this->auditLogService->record('inventory_physical_count', 'physical_count_created', [], $count->toArray(), $count->branch_id, 'Physical count created');

        return $count->load('items');
    }

    public function submit(PhysicalCount $count): PhysicalCount
    {
        $count->update([
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $this->auditLogService->record('inventory_physical_count', 'physical_count_submitted', [], $count->toArray(), $count->branch_id, 'Physical count submitted');

        return $count;
    }

    public function createAdjustmentFromVariance(PhysicalCount $count)
    {
        $items = $count->items
            ->filter(fn ($item) => $item->variance !== 0)
            ->map(fn ($item) => [
                'product_id' => $item->product_id,
                'quantity_after' => $item->counted_quantity,
                'remarks' => 'Generated from physical count '.$count->count_number,
            ])->values()->toArray();

        if (empty($items)) {
            return null;
        }

        return $this->adjustmentService->create([
            'adjustment_number' => 'ADJ-PC-'.now()->format('YmdHis'),
            'branch_id' => $count->branch_id,
            'reason' => 'Physical count discrepancy',
            'remarks' => 'Generated from physical count '.$count->count_number,
            'status' => 'pending',
            'items' => $items,
        ]);
    }
}
