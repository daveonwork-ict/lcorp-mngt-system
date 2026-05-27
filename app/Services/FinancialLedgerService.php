<?php

namespace App\Services;

use App\Models\FinancialLedger;

class FinancialLedgerService
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function record(array $payload): FinancialLedger
    {
        $lastBalance = (float) FinancialLedger::query()
            ->where('branch_id', $payload['branch_id'])
            ->latest('id')
            ->value('running_balance');

        $amountIn = (float) ($payload['amount_in'] ?? 0);
        $amountOut = (float) ($payload['amount_out'] ?? 0);

        $entry = FinancialLedger::query()->create([
            'branch_id' => $payload['branch_id'],
            'ledger_type' => $payload['ledger_type'],
            'reference_type' => $payload['reference_type'],
            'reference_id' => $payload['reference_id'] ?? null,
            'amount_in' => $amountIn,
            'amount_out' => $amountOut,
            'running_balance' => round($lastBalance + $amountIn - $amountOut, 2),
            'description' => $payload['description'] ?? null,
            'performed_by' => $payload['performed_by'] ?? auth()->id(),
        ]);

        return $entry;
    }
}
