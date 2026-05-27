<?php

namespace App\Services;

use App\Models\AirtimeWallet;
use App\Models\AirtimeWalletAdjustment;
use Illuminate\Support\Facades\DB;

class AirtimeWalletAdjustmentService
{
    public function __construct(
        private readonly AirtimeWalletService $walletService,
        private readonly NotificationService $notificationService,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    public function paginate(array $filters = [])
    {
        return AirtimeWalletAdjustment::query()
            ->with(['wallet', 'branch', 'provider'])
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
    }

    public function request(array $payload): AirtimeWalletAdjustment
    {
        $wallet = AirtimeWallet::query()->findOrFail($payload['wallet_id']);

        $model = AirtimeWalletAdjustment::query()->create([
            'adjustment_number' => $payload['adjustment_number'] ?? ('AA-'.now()->format('YmdHis').'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT)),
            'wallet_id' => $wallet->id,
            'branch_id' => $wallet->branch_id,
            'provider_id' => $wallet->provider_id,
            'adjustment_type' => $payload['adjustment_type'],
            'amount' => $payload['amount'],
            'reason' => $payload['reason'],
            'status' => 'pending',
            'requested_by' => auth()->id(),
            'remarks' => $payload['remarks'] ?? null,
        ]);

        $this->notificationService->create(null, $wallet->branch_id, 'Wallet adjustment pending', 'Wallet adjustment '.$model->adjustment_number.' needs approval.', 'airtime', ['adjustment_id' => $model->id]);
        $this->auditLogService->record('airtime', 'wallet_adjustment_requested', [], $model->toArray(), $wallet->branch_id, 'Wallet adjustment requested');

        return $model;
    }

    public function approve(AirtimeWalletAdjustment $adjustment, ?string $remarks = null): AirtimeWalletAdjustment
    {
        return DB::transaction(function () use ($adjustment, $remarks): AirtimeWalletAdjustment {
            if ($adjustment->status !== 'pending') {
                abort(422, 'Only pending adjustments can be approved.');
            }

            if ($adjustment->adjustment_type === 'increase') {
                $this->walletService->applyMovement(
                    $adjustment->wallet,
                    'adjustment',
                    (float) $adjustment->amount,
                    0,
                    'airtime_wallet_adjustment',
                    $adjustment->id,
                    'Wallet adjustment increase'
                );
            } else {
                $this->walletService->applyMovement(
                    $adjustment->wallet,
                    'adjustment',
                    0,
                    (float) $adjustment->amount,
                    'airtime_wallet_adjustment',
                    $adjustment->id,
                    'Wallet adjustment decrease'
                );
            }

            $adjustment->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'approval_remarks' => $remarks,
            ]);

            $this->auditLogService->record('airtime', 'wallet_adjustment_approved', [], $adjustment->toArray(), $adjustment->branch_id, 'Wallet adjustment approved');

            return $adjustment;
        });
    }

    public function reject(AirtimeWalletAdjustment $adjustment, ?string $remarks = null): AirtimeWalletAdjustment
    {
        if ($adjustment->status !== 'pending') {
            abort(422, 'Only pending adjustments can be rejected.');
        }

        $adjustment->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'approval_remarks' => $remarks,
        ]);

        $this->auditLogService->record('airtime', 'wallet_adjustment_rejected', [], $adjustment->toArray(), $adjustment->branch_id, 'Wallet adjustment rejected');

        return $adjustment;
    }
}
