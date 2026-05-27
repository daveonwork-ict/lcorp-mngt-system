<?php

namespace App\Services;

use App\Models\AirtimeWallet;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AirtimeWalletService
{
    public function __construct(
        private readonly BranchAccessService $branchAccessService,
        private readonly WalletLedgerService $walletLedgerService,
        private readonly AirtimeAlertService $alertService,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $user = auth()->user();

        return AirtimeWallet::query()
            ->with(['branch', 'provider'])
            ->when($user && $user->role?->code !== config('rms.owner_role_code'), function ($q) use ($user): void {
                $branchIds = $this->branchAccessService->accessibleBranches($user)->pluck('id')->all();
                $q->whereIn('branch_id', $branchIds ?: [-1]);
            })
            ->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->when($filters['provider_id'] ?? null, fn ($q, $providerId) => $q->where('provider_id', $providerId))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
    }

    public function create(array $data): AirtimeWallet
    {
        $this->assertBranchAccess((int) $data['branch_id']);

        $wallet = AirtimeWallet::query()->create([
            'wallet_number' => $data['wallet_number'],
            'branch_id' => $data['branch_id'],
            'provider_id' => $data['provider_id'],
            'beginning_balance' => $data['beginning_balance'],
            'current_balance' => $data['beginning_balance'],
            'low_balance_threshold' => $data['low_balance_threshold'] ?? 1000,
            'status' => $data['status'] ?? 'active',
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        $this->walletLedgerService->record(
            $wallet,
            'beginning_balance',
            (float) $wallet->beginning_balance,
            0,
            'airtime_wallet',
            $wallet->id,
            'Wallet created with beginning balance'
        );

        $this->auditLogService->record('airtime', 'wallet_created', [], $wallet->toArray(), $wallet->branch_id, 'Airtime wallet created');

        return $wallet;
    }

    public function update(AirtimeWallet $wallet, array $data): AirtimeWallet
    {
        $this->assertBranchAccess((int) $wallet->branch_id);

        $before = $wallet->toArray();
        $wallet->update($data + ['updated_by' => auth()->id()]);

        $this->auditLogService->record('airtime', 'wallet_updated', $before, $wallet->toArray(), $wallet->branch_id, 'Airtime wallet updated');

        return $wallet;
    }

    public function applyMovement(
        AirtimeWallet $wallet,
        string $movementType,
        float $amountIn,
        float $amountOut,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $remarks = null
    ): AirtimeWallet {
        $this->assertBranchAccess((int) $wallet->branch_id);

        $before = $wallet->toArray();
        $nextBalance = (float) $wallet->current_balance + $amountIn - $amountOut;
        if ($nextBalance < 0) {
            abort(422, 'Wallet balance cannot be negative.');
        }

        $wallet->update([
            'current_balance' => round($nextBalance, 2),
            'updated_by' => auth()->id(),
        ]);

        $this->walletLedgerService->record($wallet, $movementType, $amountIn, $amountOut, $referenceType, $referenceId, $remarks);
        $this->alertService->refreshLowBalance($wallet);
        $this->auditLogService->record('airtime', 'wallet_moved', $before, $wallet->toArray(), $wallet->branch_id, 'Wallet balance movement: '.$movementType);

        return $wallet->fresh();
    }

    private function assertBranchAccess(int $branchId): void
    {
        $user = auth()->user();
        if (! $user) {
            abort(401);
        }

        if (! $this->branchAccessService->canAccessBranch($user, $branchId)) {
            abort(403, 'Branch access denied.');
        }
    }
}
