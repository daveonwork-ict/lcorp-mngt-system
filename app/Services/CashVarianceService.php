<?php

namespace App\Services;

use App\Models\CashVariance;

class CashVarianceService
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function paginate(array $filters = [])
    {
        return CashVariance::query()
            ->with(['dailyClosing', 'branch', 'cashier', 'resolver'])
            ->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->when($filters['resolution_status'] ?? null, fn ($q, $status) => $q->where('resolution_status', $status))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
    }

    public function resolve(CashVariance $variance, string $status, ?string $explanation = null): CashVariance
    {
        $before = $variance->toArray();

        $variance->update([
            'resolution_status' => $status,
            'explanation' => $explanation ?: $variance->explanation,
            'resolved_by' => auth()->id(),
            'resolved_at' => now(),
        ]);

        $this->auditLogService->record('finance', 'cash_variance_resolved', $before, $variance->toArray(), $variance->branch_id, 'Cash variance resolution updated');

        return $variance;
    }
}
