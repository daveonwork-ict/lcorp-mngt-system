<?php

namespace App\Services;

use App\Models\AirtimeWalletFunding;
use App\Models\CashOut;

class CashOutService
{
    public function __construct(
        private readonly BranchAccessService $branchAccessService,
        private readonly AuditLogService $auditLogService,
        private readonly FinancialLedgerService $financialLedgerService,
    ) {
    }

    public function paginate(array $filters = [])
    {
        $this->syncAirtimeFundingCashOuts();

        $user = auth()->user();

        return CashOut::query()
            ->with(['branch', 'paymentMethod', 'releaser'])
            ->when(! $user || $user->role?->code !== config('rms.owner_role_code'), function ($query) use ($user): void {
                $ids = $user ? $this->branchAccessService->accessibleBranches($user)->pluck('id')->all() : [];
                $query->whereIn('branch_id', $ids ?: [-1]);
            })
            ->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->when($filters['source_type'] ?? null, fn ($q, $source) => $q->where('source_type', $source))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
    }

    public function record(array $payload): CashOut
    {
        $entry = CashOut::query()->create([
            'cash_out_number' => $payload['cash_out_number'] ?? ('COU-'.now()->format('YmdHis').'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT)),
            'branch_id' => $payload['branch_id'],
            'source_type' => $payload['source_type'],
            'source_reference_type' => $payload['source_reference_type'] ?? null,
            'source_reference_id' => $payload['source_reference_id'] ?? null,
            'amount' => $payload['amount'],
            'payment_method_id' => $payload['payment_method_id'] ?? null,
            'released_by' => $payload['released_by'] ?? auth()->id(),
            'released_at' => $payload['released_at'] ?? now(),
            'remarks' => $payload['remarks'] ?? null,
            'status' => $payload['status'] ?? 'posted',
        ]);

        $this->financialLedgerService->record([
            'branch_id' => $entry->branch_id,
            'ledger_type' => 'cash_out',
            'reference_type' => 'cash_out',
            'reference_id' => $entry->id,
            'amount_in' => 0,
            'amount_out' => $entry->amount,
            'description' => 'Cash-out '.$entry->cash_out_number.' recorded.',
        ]);

        $this->auditLogService->record('finance', 'cash_out_recorded', [], $entry->toArray(), $entry->branch_id, 'Cash-out recorded');

        return $entry;
    }

    public function syncAirtimeFundingCashOuts(): void
    {
        AirtimeWalletFunding::query()
            ->where('status', 'approved')
            ->get()
            ->each(function (AirtimeWalletFunding $funding): void {
                $exists = CashOut::query()
                    ->where('source_reference_type', 'airtime_funding')
                    ->where('source_reference_id', $funding->id)
                    ->exists();

                if (! $exists) {
                    $this->record([
                        'branch_id' => $funding->branch_id,
                        'source_type' => 'load_wallet_funding',
                        'source_reference_type' => 'airtime_funding',
                        'source_reference_id' => $funding->id,
                        'amount' => $funding->amount,
                        'released_by' => $funding->approved_by,
                        'released_at' => $funding->approved_at ?? now(),
                        'remarks' => 'Auto-posted from wallet funding '.$funding->funding_number,
                    ]);
                }
            });
    }
}
