<?php

namespace App\Services;

use App\Models\InventoryTransfer;
use Illuminate\Support\Facades\DB;

class InventoryTransferService
{
    public function __construct(
        private readonly InventoryService $inventoryService,
        private readonly ProductService $productService,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    public function create(array $payload): InventoryTransfer
    {
        if ((int) $payload['source_branch_id'] === (int) $payload['destination_branch_id']) {
            abort(422, 'Source and destination branches must be different.');
        }

        $transfer = InventoryTransfer::query()->create([
            'transfer_number' => $payload['transfer_number'],
            'source_branch_id' => $payload['source_branch_id'],
            'destination_branch_id' => $payload['destination_branch_id'],
            'remarks' => $payload['remarks'] ?? null,
            'status' => $payload['status'] ?? 'pending_approval',
            'requested_by' => auth()->id(),
        ]);

        foreach ($payload['items'] as $item) {
            $transfer->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'imei_id' => $item['imei_id'] ?? null,
                'remarks' => $item['remarks'] ?? null,
            ]);
        }

        $this->auditLogService->record('inventory_transfer', 'transfer_requested', [], $transfer->toArray(), $transfer->source_branch_id, 'Transfer requested');

        return $transfer->load('items');
    }

    public function approve(InventoryTransfer $transfer): InventoryTransfer
    {
        return DB::transaction(function () use ($transfer): InventoryTransfer {
            foreach ($transfer->items as $item) {
                $this->inventoryService->adjustStock(
                    $transfer->source_branch_id,
                    $item->product_id,
                    -1 * (int) $item->quantity,
                    0,
                    'stock_transfer_out',
                    'inventory_transfer',
                    $transfer->id,
                    'Transfer approved and moved to in_transit'
                );

                if ($item->imei_id) {
                    $item->productImei?->update([
                        'status' => 'transferred',
                        'current_reference_type' => 'inventory_transfer',
                        'current_reference_id' => $transfer->id,
                    ]);
                }
            }

            $transfer->update([
                'status' => 'in_transit',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            $this->auditLogService->record('inventory_transfer', 'transfer_approved', [], $transfer->toArray(), $transfer->source_branch_id, 'Transfer approved');

            return $transfer->fresh('items');
        });
    }

    public function receive(InventoryTransfer $transfer): InventoryTransfer
    {
        return DB::transaction(function () use ($transfer): InventoryTransfer {
            foreach ($transfer->items as $item) {
                $this->inventoryService->adjustStock(
                    $transfer->destination_branch_id,
                    $item->product_id,
                    (int) $item->quantity,
                    0,
                    'stock_transfer_in',
                    'inventory_transfer',
                    $transfer->id,
                    'Transfer received'
                );
            }

            $transfer->update([
                'status' => 'received',
                'received_by' => auth()->id(),
                'received_at' => now(),
            ]);

            $this->auditLogService->record('inventory_transfer', 'transfer_received', [], $transfer->toArray(), $transfer->destination_branch_id, 'Transfer received');

            return $transfer->fresh('items');
        });
    }
}
