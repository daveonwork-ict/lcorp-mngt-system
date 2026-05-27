<?php

namespace App\Services;

use App\Models\CashOut;
use App\Models\SupplierPayment;

class PurchasingFinanceIntegrationService
{
    public function createCashOutForSupplierPayment(SupplierPayment $payment): CashOut
    {
        return CashOut::query()->create([
            'cash_out_number' => 'CO-'.now()->format('YmdHis').'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT),
            'branch_id' => $payment->branch_id,
            'source_type' => 'supplier_payment',
            'source_reference_type' => 'supplier_payment',
            'source_reference_id' => $payment->id,
            'amount' => $payment->amount_paid,
            'payment_method_id' => $payment->payment_method_id,
            'released_by' => auth()->id(),
            'released_at' => now(),
            'remarks' => 'Auto-generated from supplier payment '.$payment->payment_number,
            'status' => 'posted',
        ]);
    }

    public function createLedgerForSupplierPayment(SupplierPayment $payment): void
    {
        $previous = (float) \App\Models\FinancialLedger::query()->where('branch_id', $payment->branch_id)->latest('id')->value('running_balance');
        $running = $previous - (float) $payment->amount_paid;

        \App\Models\FinancialLedger::query()->create([
            'branch_id' => $payment->branch_id,
            'ledger_type' => 'ap_payment',
            'reference_type' => 'supplier_payment',
            'reference_id' => $payment->id,
            'amount_in' => 0,
            'amount_out' => $payment->amount_paid,
            'running_balance' => $running,
            'description' => 'Supplier payment '.$payment->payment_number,
            'performed_by' => auth()->id(),
        ]);
    }
}
