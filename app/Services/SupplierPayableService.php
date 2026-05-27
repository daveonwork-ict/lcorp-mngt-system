<?php

namespace App\Services;

use App\Models\ReceivingReport;
use App\Models\SupplierPayable;

class SupplierPayableService
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function paginate(array $filters = [])
    {
        return SupplierPayable::query()
            ->with(['supplier', 'branch', 'receivingReport'])
            ->when($filters['supplier_id'] ?? null, fn ($q, $supplierId) => $q->where('supplier_id', $supplierId))
            ->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->when($filters['payment_status'] ?? null, fn ($q, $status) => $q->where('payment_status', $status))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
    }

    public function createFromReceiving(ReceivingReport $report): SupplierPayable
    {
        $existing = SupplierPayable::query()->where('receiving_report_id', $report->id)->first();
        if ($existing) {
            return $existing;
        }

        $total = (float) $report->items()->sum('subtotal');

        $payable = SupplierPayable::query()->create([
            'payable_number' => 'AP-'.now()->format('YmdHis').'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT),
            'supplier_id' => $report->supplier_id,
            'branch_id' => $report->branch_id,
            'receiving_report_id' => $report->id,
            'invoice_number' => $report->invoice_number,
            'payable_date' => $report->received_date,
            'due_date' => now()->parse($report->received_date)->addDays(30)->toDateString(),
            'total_amount' => $total,
            'amount_paid' => 0,
            'balance_amount' => $total,
            'payment_status' => 'unpaid',
            'status' => 'active',
            'remarks' => 'Generated from receiving report '.$report->receiving_number,
        ]);

        $this->auditLogService->record('purchasing', 'supplier_payable_created', [], $payable->toArray(), $payable->branch_id, 'Supplier payable generated');

        return $payable;
    }

    public function applyPayment(SupplierPayable $payable, float $amount): SupplierPayable
    {
        $newPaid = (float) $payable->amount_paid + $amount;
        $balance = (float) $payable->total_amount - $newPaid;

        $payable->update([
            'amount_paid' => $newPaid,
            'balance_amount' => max($balance, 0),
            'payment_status' => $balance <= 0 ? 'paid' : 'partial',
        ]);

        return $payable->fresh();
    }

    public function agingSummary(?int $supplierId = null): array
    {
        $base = SupplierPayable::query()->whereIn('payment_status', ['unpaid', 'partial']);
        if ($supplierId) {
            $base->where('supplier_id', $supplierId);
        }

        return [
            'current' => (float) (clone $base)->whereDate('due_date', '>=', now()->toDateString())->sum('balance_amount'),
            'over_1_30' => (float) (clone $base)->whereDate('due_date', '<', now()->toDateString())->whereDate('due_date', '>=', now()->subDays(30)->toDateString())->sum('balance_amount'),
            'over_30' => (float) (clone $base)->whereDate('due_date', '<', now()->subDays(30)->toDateString())->sum('balance_amount'),
        ];
    }
}
