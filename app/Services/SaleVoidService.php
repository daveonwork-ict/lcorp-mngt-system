<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\SaleVoidRequest;
use Illuminate\Support\Facades\DB;

class SaleVoidService
{
    public function __construct(
        private readonly SalesService $salesService,
        private readonly SalesInventoryService $inventoryService,
        private readonly SalesAuditService $salesAuditService,
        private readonly NotificationService $notificationService,
    ) {
    }

    public function request(Sale $sale, string $reason): SaleVoidRequest
    {
        $this->salesService->ensureCanAccessSale($sale);

        if ($sale->sales_status !== 'completed') {
            abort(422, 'Only completed sales can be voided.');
        }

        $void = SaleVoidRequest::query()->create([
            'sale_id' => $sale->id,
            'requested_by' => auth()->id(),
            'status' => 'pending',
            'reason' => $reason,
        ]);

        $this->salesAuditService->log('void_requested', [], $void->toArray(), $sale->branch_id, 'Sale void requested');
        $this->notificationService->create(null, $sale->branch_id, 'Void request', 'Void request pending approval for '.$sale->sales_number, 'sales', ['void_request_id' => $void->id]);

        return $void;
    }

    public function approve(SaleVoidRequest $voidRequest, string $remarks = ''): SaleVoidRequest
    {
        return DB::transaction(function () use ($voidRequest, $remarks): SaleVoidRequest {
            $sale = $voidRequest->sale()->with('items.imei')->firstOrFail();
            $this->salesService->ensureCanAccessSale($sale);

            if ($voidRequest->status !== 'pending') {
                abort(422, 'Void request is not pending.');
            }

            $this->inventoryService->restoreForVoid($sale);

            $sale->update([
                'sales_status' => 'voided',
                'payment_status' => 'cancelled',
            ]);

            $voidRequest->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'approval_remarks' => $remarks,
            ]);

            $this->salesAuditService->log('void_approved', [], $voidRequest->toArray(), $sale->branch_id, 'Sale void approved');

            return $voidRequest->fresh('sale');
        });
    }

    public function reject(SaleVoidRequest $voidRequest, string $remarks = ''): SaleVoidRequest
    {
        if ($voidRequest->status !== 'pending') {
            abort(422, 'Void request is not pending.');
        }

        $voidRequest->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'approval_remarks' => $remarks,
        ]);

        $this->salesAuditService->log('void_rejected', [], $voidRequest->toArray(), $voidRequest->sale?->branch_id, 'Sale void rejected');

        return $voidRequest;
    }
}
