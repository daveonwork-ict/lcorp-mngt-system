<?php

namespace App\Services;

use App\Models\AirtimeTransaction;
use App\Models\AirtimeWallet;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\DB;

class AirtimeTransactionService
{
    public function __construct(
        private readonly AirtimeWalletService $walletService,
        private readonly AirtimeValidationService $validationService,
        private readonly AirtimeCommissionService $commissionService,
        private readonly AirtimeAlertService $alertService,
        private readonly AirtimeCashFlowIntegrationService $cashFlowIntegration,
        private readonly NotificationService $notificationService,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    public function paginate(array $filters = [])
    {
        return AirtimeTransaction::query()
            ->with(['branch', 'provider', 'wallet', 'cashier', 'paymentMethod'])
            ->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->when($filters['provider_id'] ?? null, fn ($q, $providerId) => $q->where('provider_id', $providerId))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('transaction_status', $status))
            ->when($filters['date_from'] ?? null, fn ($q, $date) => $q->whereDate('processed_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($q, $date) => $q->whereDate('processed_at', '<=', $date))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
    }

    public function create(array $payload): AirtimeTransaction
    {
        return DB::transaction(function () use ($payload): AirtimeTransaction {
            $wallet = AirtimeWallet::query()->with('provider')->findOrFail($payload['wallet_id']);

            if ((int) $wallet->branch_id !== (int) $payload['branch_id']) {
                abort(422, 'Wallet does not belong to selected branch.');
            }

            if ((int) $wallet->provider_id !== (int) $payload['provider_id']) {
                abort(422, 'Wallet does not belong to selected provider.');
            }

            if ((float) $payload['load_amount'] <= 0) {
                abort(422, 'Load amount must be greater than zero.');
            }

            $mobile = $this->validationService->validateMobileNumber($payload['customer_mobile_number']);
            $suspicious = $this->validationService->suspiciousReasons(
                (int) $payload['branch_id'],
                (int) $payload['provider_id'],
                $mobile,
                (float) $payload['load_amount'],
                $payload['payment_reference'] ?? null
            );

            if ((float) $wallet->current_balance < (float) $payload['load_amount']) {
                $this->alertService->create(
                    $wallet->branch_id,
                    $wallet->provider_id,
                    $wallet->id,
                    null,
                    'insufficient_wallet_balance',
                    'critical',
                    'Insufficient wallet balance for load transaction attempt.',
                    ['wallet_balance' => $wallet->current_balance, 'load_amount' => $payload['load_amount']]
                );
                abort(422, 'Insufficient wallet balance.');
            }

            if (! empty($payload['payment_method_id'])) {
                PaymentMethod::query()->findOrFail($payload['payment_method_id']);
            }

            $commission = $this->commissionService->compute(
                $wallet->provider,
                (float) $payload['load_amount'],
                $payload['commission_override'] ?? null
            );

            $transaction = AirtimeTransaction::query()->create([
                'transaction_number' => $payload['transaction_number'] ?? ('AT-'.now()->format('YmdHis').'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT)),
                'branch_id' => $payload['branch_id'],
                'cashier_id' => auth()->id(),
                'provider_id' => $payload['provider_id'],
                'wallet_id' => $wallet->id,
                'customer_mobile_number' => $mobile,
                'load_amount' => $payload['load_amount'],
                'commission_amount' => $commission['commission_amount'],
                'total_amount' => $payload['load_amount'],
                'payment_method_id' => $payload['payment_method_id'] ?? null,
                'payment_reference' => $payload['payment_reference'] ?? null,
                'transaction_status' => $payload['transaction_status'] ?? 'successful',
                'remarks' => $payload['remarks'] ?? null,
                'processed_at' => now(),
            ]);

            if ($transaction->transaction_status === 'successful') {
                $this->walletService->applyMovement(
                    $wallet,
                    'load_deduction',
                    0,
                    (float) $transaction->load_amount,
                    'airtime_transaction',
                    $transaction->id,
                    'Airtime load transaction deduction'
                );
            }

            $this->commissionService->record($transaction, $commission, 'Auto computed from provider rules');
            $cashflowPayload = $this->cashFlowIntegration->prepareTransactionCashIn($transaction);

            $this->auditLogService->record('airtime', 'load_transaction_created', [], $transaction->toArray(), $transaction->branch_id, 'Airtime load transaction created');

            if (! empty($suspicious)) {
                foreach ($suspicious as $reason) {
                    $this->alertService->suspicious($transaction, $reason);
                }
                $this->notificationService->create(null, $transaction->branch_id, 'Suspicious load transaction', 'Suspicious activity detected for '.$transaction->transaction_number, 'airtime', ['transaction_id' => $transaction->id, 'reasons' => $suspicious]);
            }

            if ($transaction->transaction_status === 'failed') {
                $this->alertService->create($transaction->branch_id, $transaction->provider_id, $transaction->wallet_id, $transaction->id, 'failed_transaction', 'high', 'Airtime transaction failed.', ['transaction_id' => $transaction->id]);
            }

            if ((float) $transaction->load_amount >= (float) config('rms.airtime.high_value_threshold', 1000)) {
                $this->alertService->create($transaction->branch_id, $transaction->provider_id, $transaction->wallet_id, $transaction->id, 'high_value_transaction', 'medium', 'High-value airtime transaction.', ['amount' => $transaction->load_amount]);
            }

            $transaction->update(['cashflow_payload' => $cashflowPayload]);

            return $transaction->fresh(['branch', 'provider', 'wallet', 'paymentMethod']);
        });
    }

    public function reverse(AirtimeTransaction $transaction, string $reason): AirtimeTransaction
    {
        return DB::transaction(function () use ($transaction, $reason): AirtimeTransaction {
            if (! in_array($transaction->transaction_status, ['successful', 'pending'], true)) {
                abort(422, 'Only successful or pending transactions can be reversed.');
            }

            $wallet = $transaction->wallet()->firstOrFail();

            if ($transaction->transaction_status === 'successful') {
                $this->walletService->applyMovement(
                    $wallet,
                    'reversal',
                    (float) $transaction->load_amount,
                    0,
                    'airtime_transaction',
                    $transaction->id,
                    'Airtime transaction reversal'
                );
            }

            $transaction->update([
                'transaction_status' => 'reversed',
                'reversed_at' => now(),
                'reversal_reason' => $reason,
            ]);

            $this->alertService->create($transaction->branch_id, $transaction->provider_id, $transaction->wallet_id, $transaction->id, 'reversed_transaction', 'high', 'Airtime transaction reversed.', ['transaction_id' => $transaction->id, 'reason' => $reason]);
            $this->auditLogService->record('airtime', 'transaction_reversed', [], $transaction->toArray(), $transaction->branch_id, 'Airtime transaction reversed');

            return $transaction;
        });
    }
}
