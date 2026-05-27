<?php

namespace App\Services;

use App\Models\ProductImei;
use App\Models\StockIn;
use Illuminate\Support\Facades\DB;

class StockInService
{
    public function __construct(
        private readonly InventoryService $inventoryService,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    public function create(array $payload): StockIn
    {
        return DB::transaction(function () use ($payload): StockIn {
            $stockIn = StockIn::query()->create([
                'stock_in_number' => $payload['stock_in_number'],
                'branch_id' => $payload['branch_id'],
                'supplier_id' => $payload['supplier_id'] ?? null,
                'received_date' => $payload['received_date'],
                'reference_number' => $payload['reference_number'] ?? null,
                'delivery_receipt_number' => $payload['delivery_receipt_number'] ?? null,
                'remarks' => $payload['remarks'] ?? null,
                'received_by' => auth()->id(),
                'status' => $payload['status'] ?? 'pending',
            ]);

            foreach ($payload['items'] as $item) {
                $createdItem = $stockIn->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'cost_price' => $item['cost_price'],
                    'selling_price' => $item['selling_price'],
                    'subtotal' => $item['quantity'] * $item['cost_price'],
                    'remarks' => $item['remarks'] ?? null,
                ]);

                if (($item['imeis'] ?? null) && is_array($item['imeis'])) {
                    foreach ($item['imeis'] as $imeiNumber) {
                        ProductImei::query()->create([
                            'product_id' => $item['product_id'],
                            'branch_id' => $stockIn->branch_id,
                            'imei_number' => $imeiNumber,
                            'status' => 'available',
                            'received_date' => $stockIn->received_date,
                            'current_reference_type' => 'stock_in',
                            'current_reference_id' => $stockIn->id,
                        ]);
                    }
                }
            }

            $this->auditLogService->record('inventory_stock_in', 'stock_in_created', [], $stockIn->toArray(), $stockIn->branch_id, 'Stock-in created');

            if ($stockIn->status === 'approved') {
                $this->approve($stockIn);
            }

            return $stockIn->load('items');
        });
    }

    public function approve(StockIn $stockIn): StockIn
    {
        if ($stockIn->status === 'approved') {
            return $stockIn;
        }

        return DB::transaction(function () use ($stockIn): StockIn {
            foreach ($stockIn->items as $item) {
                $this->inventoryService->adjustStock(
                    $stockIn->branch_id,
                    $item->product_id,
                    $item->quantity,
                    (float) $item->cost_price,
                    'stock_in',
                    'stock_in',
                    $stockIn->id,
                    'Stock-in approved'
                );
            }

            $stockIn->update(['status' => 'approved']);

            $this->auditLogService->record('inventory_stock_in', 'stock_in_approved', [], $stockIn->toArray(), $stockIn->branch_id, 'Stock-in approved');

            return $stockIn->fresh('items');
        });
    }
}
