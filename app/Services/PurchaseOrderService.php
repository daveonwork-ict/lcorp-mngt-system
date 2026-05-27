<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use Illuminate\Support\Facades\DB;

class PurchaseOrderService
{
    public function __construct(
        private readonly BranchAccessService $branchAccessService,
        private readonly AuditLogService $auditLogService,
        private readonly NotificationService $notificationService,
    ) {
    }

    public function paginate(array $filters = [])
    {
        $branchIds = $this->allowedBranchIds();

        return PurchaseOrder::query()
            ->with(['supplier', 'branch', 'request'])
            ->when($branchIds !== null, fn ($q) => $q->whereIn('branch_id', $branchIds))
            ->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->when($filters['supplier_id'] ?? null, fn ($q, $supplierId) => $q->where('supplier_id', $supplierId))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
    }

    public function create(array $payload): PurchaseOrder
    {
        return DB::transaction(function () use ($payload): PurchaseOrder {
            $request = null;
            if (! empty($payload['request_id'])) {
                $request = PurchaseRequest::query()->with('items')->findOrFail($payload['request_id']);
                if ($request->status !== 'approved') {
                    abort(422, 'Only approved purchase requests can be converted to PO.');
                }
            }

            $order = PurchaseOrder::query()->create([
                'po_number' => $payload['po_number'] ?? ('PO-'.now()->format('YmdHis').'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT)),
                'supplier_id' => $payload['supplier_id'],
                'branch_id' => $payload['branch_id'],
                'request_id' => $payload['request_id'] ?? null,
                'po_date' => $payload['po_date'] ?? now()->toDateString(),
                'expected_delivery_date' => $payload['expected_delivery_date'] ?? null,
                'status' => 'pending_approval',
                'prepared_by' => auth()->id(),
                'remarks' => $payload['remarks'] ?? null,
            ]);

            $items = $payload['items'] ?? [];
            if ($request && empty($items)) {
                $items = $request->items->map(fn ($item) => [
                    'product_id' => $item->product_id,
                    'quantity_ordered' => $item->requested_quantity,
                    'unit_cost' => $item->estimated_cost ?? 0,
                ])->all();
            }

            $total = 0;
            foreach ($items as $item) {
                $subtotal = (float) $item['quantity_ordered'] * (float) $item['unit_cost'];
                $total += $subtotal;
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity_ordered' => $item['quantity_ordered'],
                    'unit_cost' => $item['unit_cost'],
                    'subtotal' => $subtotal,
                    'remarks' => $item['remarks'] ?? null,
                ]);
            }

            $order->update(['total_amount' => $total]);

            $this->auditLogService->record('purchasing', 'purchase_order_created', [], $order->toArray(), $order->branch_id, 'Purchase order created');

            return $order->fresh(['items']);
        });
    }

    public function approve(PurchaseOrder $order): PurchaseOrder
    {
        if ($order->status !== 'pending_approval') {
            abort(422, 'Purchase order is not pending approval.');
        }

        $order->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        if ($order->request) {
            $order->request->update(['converted_at' => now()]);
        }

        $this->notificationService->create(null, $order->branch_id, 'PO approved', 'Purchase order '.$order->po_number.' approved.', 'purchasing', ['purchase_order_id' => $order->id]);
        $this->auditLogService->record('purchasing', 'purchase_order_approved', [], $order->toArray(), $order->branch_id, 'Purchase order approved');

        return $order->fresh();
    }

    public function markSent(PurchaseOrder $order): PurchaseOrder
    {
        if (! in_array($order->status, ['approved', 'sent'], true)) {
            abort(422, 'Only approved PO can be sent.');
        }

        $order->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        return $order;
    }

    private function allowedBranchIds(): ?array
    {
        $user = auth()->user();
        if (! $user || $user->role?->code === config('rms.owner_role_code')) {
            return null;
        }

        $ids = $this->branchAccessService->accessibleBranches($user)->pluck('id')->all();

        return $ids ?: [-1];
    }
}
