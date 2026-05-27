<?php

namespace App\Services;

use App\Models\AirtimeAlert;
use App\Models\AirtimeTransaction;
use App\Models\AirtimeWallet;

class AirtimeAlertService
{
    public function __construct(private readonly NotificationService $notificationService)
    {
    }

    public function create(
        ?int $branchId,
        ?int $providerId,
        ?int $walletId,
        ?int $transactionId,
        string $alertType,
        string $severity,
        string $message,
        array $payload = []
    ): AirtimeAlert {
        $alert = AirtimeAlert::query()->create([
            'branch_id' => $branchId,
            'provider_id' => $providerId,
            'wallet_id' => $walletId,
            'transaction_id' => $transactionId,
            'alert_type' => $alertType,
            'severity' => $severity,
            'message' => $message,
            'payload' => $payload ?: null,
        ]);

        $this->notificationService->create(null, $branchId, 'Airtime alert', $message, 'airtime', ['alert_id' => $alert->id] + $payload);

        return $alert;
    }

    public function refreshLowBalance(AirtimeWallet $wallet): void
    {
        if ((float) $wallet->current_balance <= (float) $wallet->low_balance_threshold) {
            $this->create(
                $wallet->branch_id,
                $wallet->provider_id,
                $wallet->id,
                null,
                'low_wallet_balance',
                'high',
                'Wallet '.$wallet->wallet_number.' is at low balance.',
                ['current_balance' => $wallet->current_balance, 'threshold' => $wallet->low_balance_threshold]
            );
        }
    }

    public function suspicious(AirtimeTransaction $transaction, string $reason): void
    {
        $this->create(
            $transaction->branch_id,
            $transaction->provider_id,
            $transaction->wallet_id,
            $transaction->id,
            'suspicious_transaction',
            'critical',
            'Suspicious airtime transaction detected: '.$reason,
            ['transaction_number' => $transaction->transaction_number, 'reason' => $reason]
        );
    }
}
