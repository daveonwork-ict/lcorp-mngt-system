<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use Illuminate\Support\Facades\DB;

class SaleReturnService
{
    public function __construct(
        private readonly SalesService $salesService,
        private readonly SalesInventoryService $inventoryService,
        private readonly SalesAuditService $salesAuditService,
        private readonly NotificationService $notificationService,
    ) {
    }

    public function request(array $payload): SaleReturn
    {
        $sale = Sale::query()->with('items')->findOrFail($payload['sale_id']);
        $this->salesService->ensureCanAccessSale($sale);

        $saleReturn = SaleReturn::query()->create([
            'return_number' => $payload['return_number'] ?? ('SR-'.now()->format('YmdHis').'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT)),
            'sale_id' => $sale->id,
            'branch_id' => $sale->branch_id,
            'requested_by' => auth()->id(),
            'status' => 'pending',
            'return_type' => $payload['return_type'] ?? 'return',
            'refund_amount' => 0,
            'reason' => $payload['reason'] ?? null,
        ]);

        $totalRefund = 0;

        foreach ($payload['items'] as $item) {
            $saleItem = $sale->items->firstWhere('id', $item['sale_item_id']);
            if (! $saleItem) {
                abort(422, 'Sale item not found.');
            }

            $qty = (int) $item['quantity'];
            if ($qty <= 0 || $qty > (int) $saleItem->quantity) {
                abort(422, 'Invalid return quantity.');
            }

            $refund = round(((float) $saleItem->selling_price * $qty), 2);
            $totalRefund += $refund;

            $saleReturn->items()->create([
                'sale_item_id' => $saleItem->id,
                'quantity' => $qty,
                'item_condition' => $item['item_condition'] ?? null,
                'refund_amount' => $refund,
                'remarks' => $item['remarks'] ?? null,
            ]);
        }

        $saleReturn->update(['refund_amount' => $totalRefund]);

        $this->salesAuditService->log('return_requested', [], $saleReturn->toArray(), $sale->branch_id, 'Sale return requested');
        $this->notificationService->create(null, $sale->branch_id, 'Return request', 'Return request pending approval for '.$sale->sales_number, 'sales', ['sale_return_id' => $saleReturn->id]);

        return $saleReturn->fresh('items.saleItem.product');
    }

    public function approve(SaleReturn $saleReturn): SaleReturn
    {
        return DB::transaction(function () use ($saleReturn): SaleReturn {
            $saleReturn->load('sale', 'items.saleItem.imei');
            if ($saleReturn->status !== 'pending') {
                abort(422, 'Return request is not pending.');
            }

            foreach ($saleReturn->items as $item) {
                $this->inventoryService->restoreForReturn($item->saleItem, $saleReturn->branch_id, (int) $item->quantity);

                $item->saleItem->update([
                    'item_status' => 'refunded',
                ]);
            }

            $saleReturn->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            $sale = $saleReturn->sale;
            $sale->update([
                'sales_status' => 'partially_refunded',
                'payment_status' => 'refunded',
            ]);

            $this->salesAuditService->log('return_approved', [], $saleReturn->toArray(), $saleReturn->branch_id, 'Sale return approved');

            return $saleReturn->fresh('sale', 'items.saleItem.product');
        });
    }

    public function reject(SaleReturn $saleReturn): SaleReturn
    {
        if ($saleReturn->status !== 'pending') {
            abort(422, 'Return request is not pending.');
        }

        $saleReturn->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $this->salesAuditService->log('return_rejected', [], $saleReturn->toArray(), $saleReturn->branch_id, 'Sale return rejected');

        return $saleReturn;
    }
}
