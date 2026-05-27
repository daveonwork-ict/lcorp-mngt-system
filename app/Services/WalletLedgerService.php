<?php

namespace App\Services;

use App\Models\AirtimeWallet;
use App\Models\AirtimeWalletLedger;

class WalletLedgerService
{
    public function record(
        AirtimeWallet $wallet,
        string $movementType,
        float $amountIn,
        float $amountOut,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $remarks = null
    ): AirtimeWalletLedger {
        return AirtimeWalletLedger::query()->create([
            'wallet_id' => $wallet->id,
            'branch_id' => $wallet->branch_id,
            'provider_id' => $wallet->provider_id,
            'movement_type' => $movementType,
            'amount_in' => $amountIn,
            'amount_out' => $amountOut,
            'running_balance' => $wallet->current_balance,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'remarks' => $remarks,
            'performed_by' => auth()->id(),
        ]);
    }
}
