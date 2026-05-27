<?php

namespace App\Services;

use App\Models\OfficeSupplyInventory;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\ReceivingReport;
use App\Models\SupplierPayable;

class PurchasingReportService
{
    public function dashboardCards(?int $branchId = null): array
    {
        $pr = PurchaseRequest::query()->when($branchId, fn ($q) => $q->where('branch_id', $branchId));
        $po = PurchaseOrder::query()->when($branchId, fn ($q) => $q->where('branch_id', $branchId));
        $rr = ReceivingReport::query()->when($branchId, fn ($q) => $q->where('branch_id', $branchId));
        $ap = SupplierPayable::query()->when($branchId, fn ($q) => $q->where('branch_id', $branchId));
        $os = OfficeSupplyInventory::query()->when($branchId, fn ($q) => $q->where('branch_id', $branchId));

        return [
            'pending_pr' => (int) (clone $pr)->where('status', 'pending_approval')->count(),
            'pending_po' => (int) (clone $po)->where('status', 'pending_approval')->count(),
            'items_waiting_receipt' => (int) (clone $po)->whereIn('status', ['approved', 'sent', 'partial_received'])->count(),
            'total_payables' => (float) (clone $ap)->sum('balance_amount'),
            'overdue_payables' => (float) (clone $ap)->whereDate('due_date', '<', now()->toDateString())->whereIn('payment_status', ['unpaid', 'partial'])->sum('balance_amount'),
            'office_supply_low_stock' => (int) (clone $os)->whereColumn('quantity_on_hand', '<=', 'reorder_level')->count(),
            'receiving_mtd' => (int) (clone $rr)->whereDate('received_date', '>=', now()->startOfMonth()->toDateString())->count(),
        ];
    }

    public function purchaseRequestReport(array $filters = [])
    {
        return PurchaseRequest::query()
            ->with(['branch', 'requester', 'approver'])
            ->when($filters['date_from'] ?? null, fn ($q, $date) => $q->whereDate('request_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($q, $date) => $q->whereDate('request_date', '<=', $date))
            ->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
    }

    public function payableAgingReport(array $filters = [])
    {
        return SupplierPayable::query()
            ->with(['supplier', 'branch'])
            ->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->when($filters['supplier_id'] ?? null, fn ($q, $supplierId) => $q->where('supplier_id', $supplierId))
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->orderBy('due_date')
            ->paginate(20)
            ->withQueryString();
    }

    public function officeSupplyUsageReport(array $filters = [])
    {
        return \App\Models\OfficeSupplyIssuance::query()
            ->with(['branch', 'recipient', 'items.supply'])
            ->when($filters['date_from'] ?? null, fn ($q, $date) => $q->whereDate('issue_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($q, $date) => $q->whereDate('issue_date', '<=', $date))
            ->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->where('status', 'issued')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
    }
}
