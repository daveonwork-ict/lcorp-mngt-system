<?php

namespace App\Services;

use App\Models\AirtimeTransaction;
use App\Models\AirtimeWalletFunding;

class AirtimeCashFlowIntegrationService
{
    public function prepareTransactionCashIn(AirtimeTransaction $transaction): array
    {
        if ($transaction->cashflow_prepared) {
            return (array) $transaction->cashflow_payload;
        }

        $payload = [
            'entry_type' => 'cash_in',
            'module' => 'airtime',
            'reference_type' => 'airtime_transaction',
            'reference_id' => $transaction->id,
            'reference_number' => $transaction->transaction_number,
            'branch_id' => $transaction->branch_id,
            'user_id' => $transaction->cashier_id,
            'payment_method_id' => $transaction->payment_method_id,
            'amount' => $transaction->total_amount,
            'prepared_at' => now()->toDateTimeString(),
        ];

        $transaction->update([
            'cashflow_prepared' => true,
            'cashflow_payload' => $payload,
        ]);

        return $payload;
    }

    public function prepareFundingCashOut(AirtimeWalletFunding $funding): array
    {
        if ($funding->cashflow_prepared) {
            return (array) $funding->cashflow_payload;
        }

        $payload = [
            'entry_type' => 'cash_out',
            'module' => 'airtime',
            'reference_type' => 'airtime_funding',
            'reference_id' => $funding->id,
            'reference_number' => $funding->funding_number,
            'branch_id' => $funding->branch_id,
            'user_id' => $funding->requested_by,
            'amount' => $funding->amount,
            'payment_method' => $funding->payment_method,
            'prepared_at' => now()->toDateTimeString(),
        ];

        $funding->update([
            'cashflow_prepared' => true,
            'cashflow_payload' => $payload,
        ]);

        return $payload;
    }
}
