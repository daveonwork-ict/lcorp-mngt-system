<?php

namespace App\Services;

use App\Models\AirtimeWallet;
use App\Models\AirtimeWalletFunding;
use Illuminate\Support\Facades\DB;

class AirtimeFundingService
{
    public function __construct(
        private readonly AirtimeWalletService $walletService,
        private readonly AirtimeCashFlowIntegrationService $cashFlowIntegration,
        private readonly NotificationService $notificationService,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    public function paginate(array $filters = [])
    {
        return AirtimeWalletFunding::query()
            ->with(['wallet', 'branch', 'provider', 'requester', 'approver'])
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
    }

    public function request(array $payload): AirtimeWalletFunding
    {
        $wallet = AirtimeWallet::query()->findOrFail($payload['wallet_id']);

        $funding = AirtimeWalletFunding::query()->create([
            'funding_number' => $payload['funding_number'] ?? ('AF-'.now()->format('YmdHis').'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT)),
            'wallet_id' => $wallet->id,
            'branch_id' => $wallet->branch_id,
            'provider_id' => $wallet->provider_id,
            'amount' => $payload['amount'],
            'funding_date' => $payload['funding_date'],
            'payment_method' => $payload['payment_method'],
            'reference_number' => $payload['reference_number'] ?? null,
            'proof_file' => $payload['proof_file'] ?? null,
            'status' => $payload['status'] ?? 'pending',
            'requested_by' => auth()->id(),
            'remarks' => $payload['remarks'] ?? null,
        ]);

        $this->notificationService->create(null, $wallet->branch_id, 'Wallet funding request', 'Wallet funding request '.$funding->funding_number.' submitted.', 'airtime', ['funding_id' => $funding->id]);
        $this->auditLogService->record('airtime', 'wallet_funding_requested', [], $funding->toArray(), $wallet->branch_id, 'Wallet funding requested');

        return $funding;
    }

    public function approve(AirtimeWalletFunding $funding): AirtimeWalletFunding
    {
        return DB::transaction(function () use ($funding): AirtimeWalletFunding {
            if ($funding->status !== 'pending') {
                abort(422, 'Only pending funding can be approved.');
            }

            $this->walletService->applyMovement(
                $funding->wallet,
                'wallet_funding',
                (float) $funding->amount,
                0,
                'airtime_wallet_funding',
                $funding->id,
                'Wallet funding approved'
            );

            $funding->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            $payload = $this->cashFlowIntegration->prepareFundingCashOut($funding);
            $this->auditLogService->record('airtime', 'wallet_funding_approved', [], $funding->toArray(), $funding->branch_id, 'Wallet funding approved');

            $this->notificationService->create(
                $funding->requested_by,
                $funding->branch_id,
                'Wallet funding approved',
                'Funding '.$funding->funding_number.' approved.',
                'airtime',
                ['funding_id' => $funding->id, 'cashflow' => $payload]
            );

            return $funding->fresh();
        });
    }

    public function reject(AirtimeWalletFunding $funding, string $reason): AirtimeWalletFunding
    {
        if ($funding->status !== 'pending') {
            abort(422, 'Only pending funding can be rejected.');
        }

        $funding->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);

        $this->auditLogService->record('airtime', 'wallet_funding_rejected', [], $funding->toArray(), $funding->branch_id, 'Wallet funding rejected');
        $this->notificationService->create($funding->requested_by, $funding->branch_id, 'Wallet funding rejected', 'Funding '.$funding->funding_number.' was rejected.', 'airtime', ['funding_id' => $funding->id]);

        return $funding;
    }
}
