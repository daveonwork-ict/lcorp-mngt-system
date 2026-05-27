<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\ReceivingReport;
use Illuminate\Support\Facades\DB;

class ReceivingReportService
{
    public function __construct(
        private readonly PurchasingInventoryIntegrationService $inventoryIntegration,
        private readonly SupplierPayableService $payableService,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    public function paginate(array $filters = [])
    {
        return ReceivingReport::query()
            ->with(['purchaseOrder', 'supplier', 'branch', 'receiver'])
            ->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->when($filters['supplier_id'] ?? null, fn ($q, $supplierId) => $q->where('supplier_id', $supplierId))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
    }

    public function create(array $payload): ReceivingReport
    {
        return DB::transaction(function () use ($payload): ReceivingReport {
            $order = PurchaseOrder::query()->with(['items', 'supplier'])->findOrFail($payload['purchase_order_id']);
            if (! in_array($order->status, ['approved', 'sent', 'partial_received'], true)) {
                abort(422, 'PO must be approved or sent before receiving.');
            }

            $report = ReceivingReport::query()->create([
                'receiving_number' => $payload['receiving_number'] ?? ('RR-'.now()->format('YmdHis').'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT)),
                'purchase_order_id' => $order->id,
                'supplier_id' => $order->supplier_id,
                'branch_id' => $order->branch_id,
                'received_date' => $payload['received_date'] ?? now()->toDateString(),
                'delivery_receipt_number' => $payload['delivery_receipt_number'] ?? null,
                'invoice_number' => $payload['invoice_number'] ?? null,
                'attachment_path' => $payload['attachment_path'] ?? null,
                'received_by' => auth()->id(),
                'status' => 'received',
                'remarks' => $payload['remarks'] ?? null,
            ]);

            $items = $payload['items'];
            foreach ($items as $item) {
                $poItem = $order->items()->where('product_id', $item['product_id'])->first();
                if (! $poItem) {
                    abort(422, 'Received product is not part of PO.');
                }

                $qty = (int) $item['quantity_received'];
                if ($qty <= 0) {
                    continue;
                }

                $unitCost = (float) ($item['unit_cost'] ?? $poItem->unit_cost);
                $reportItem = $report->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity_received' => $qty,
                    'unit_cost' => $unitCost,
                    'subtotal' => $qty * $unitCost,
                    'serialized_entries' => $item['serialized_entries'] ?? null,
                    'remarks' => $item['remarks'] ?? null,
                ]);

                $poItem->quantity_received = (int) $poItem->quantity_received + $qty;
                $poItem->save();

                $this->inventoryIntegration->receiveInventoryItem(
                    (int) $report->branch_id,
                    (int) $reportItem->product_id,
                    $qty,
                    'receiving_report',
                    (int) $report->id,
                    (array) ($item['serialized_entries'] ?? [])
                );
            }

            $this->syncOrderStatus($order);
            $this->payableService->createFromReceiving($report->fresh(['items']));
            $this->auditLogService->record('purchasing', 'receiving_report_created', [], $report->toArray(), $report->branch_id, 'Receiving report recorded');

            return $report->fresh(['items']);
        });
    }

    private function syncOrderStatus(PurchaseOrder $order): void
    {
        $ordered = (int) $order->items()->sum('quantity_ordered');
        $received = (int) $order->items()->sum('quantity_received');

        $status = 'approved';
        if ($received > 0 && $received < $ordered) {
            $status = 'partial_received';
        }
        if ($received >= $ordered && $ordered > 0) {
            $status = 'fully_received';
        }

        $order->update(['status' => $status]);
    }
}
