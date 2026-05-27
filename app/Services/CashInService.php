<?php

namespace App\Services;

use App\Models\AirtimeTransaction;
use App\Models\CashIn;
use App\Models\SalePayment;

class CashInService
{
    public function __construct(
        private readonly BranchAccessService $branchAccessService,
        private readonly AuditLogService $auditLogService,
        private readonly FinancialLedgerService $financialLedgerService,
    ) {
    }

    public function paginate(array $filters = [])
    {
        $this->syncPreparedCashIns();

        $user = auth()->user();

        return CashIn::query()
            ->with(['branch', 'paymentMethod', 'receiver'])
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

    public function record(array $payload): CashIn
    {
        $entry = CashIn::query()->create([
            'cash_in_number' => $payload['cash_in_number'] ?? ('CI-'.now()->format('YmdHis').'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT)),
            'branch_id' => $payload['branch_id'],
            'source_type' => $payload['source_type'],
            'source_reference_type' => $payload['source_reference_type'] ?? null,
            'source_reference_id' => $payload['source_reference_id'] ?? null,
            'amount' => $payload['amount'],
            'payment_method_id' => $payload['payment_method_id'] ?? null,
            'received_by' => $payload['received_by'] ?? auth()->id(),
            'received_at' => $payload['received_at'] ?? now(),
            'remarks' => $payload['remarks'] ?? null,
            'status' => $payload['status'] ?? 'posted',
        ]);

        $this->financialLedgerService->record([
            'branch_id' => $entry->branch_id,
            'ledger_type' => 'cash_in',
            'reference_type' => 'cash_in',
            'reference_id' => $entry->id,
            'amount_in' => $entry->amount,
            'amount_out' => 0,
            'description' => 'Cash-in '.$entry->cash_in_number.' recorded.',
        ]);

        $this->auditLogService->record('finance', 'cash_in_recorded', [], $entry->toArray(), $entry->branch_id, 'Cash-in recorded');

        return $entry;
    }

    public function syncPreparedCashIns(): void
    {
        SalePayment::query()
            ->with(['sale', 'paymentMethod'])
            ->whereHas('paymentMethod', fn ($q) => $q->where('payment_type', 'cash'))
            ->get()
            ->each(function (SalePayment $payment): void {
                if (! $payment->sale || $payment->sale->sales_status !== 'completed') {
                    return;
                }

                $exists = CashIn::query()
                    ->where('source_reference_type', 'sale_payment')
                    ->where('source_reference_id', $payment->id)
                    ->exists();

                if (! $exists) {
                    $this->record([
                        'branch_id' => $payment->sale->branch_id,
                        'source_type' => 'product_sales',
                        'source_reference_type' => 'sale_payment',
                        'source_reference_id' => $payment->id,
                        'amount' => $payment->amount,
                        'payment_method_id' => $payment->payment_method_id,
                        'received_by' => $payment->received_by,
                        'received_at' => $payment->received_at ?? now(),
                        'remarks' => 'Auto-posted from sale '.$payment->sale->sales_number,
                    ]);
                }
            });

        AirtimeTransaction::query()
            ->with('paymentMethod')
            ->where('transaction_status', 'successful')
            ->whereHas('paymentMethod', fn ($q) => $q->where('payment_type', 'cash'))
            ->get()
            ->each(function (AirtimeTransaction $transaction): void {
                $exists = CashIn::query()
                    ->where('source_reference_type', 'airtime_transaction')
                    ->where('source_reference_id', $transaction->id)
                    ->exists();

                if (! $exists) {
                    $this->record([
                        'branch_id' => $transaction->branch_id,
                        'source_type' => 'airtime_sales',
                        'source_reference_type' => 'airtime_transaction',
                        'source_reference_id' => $transaction->id,
                        'amount' => $transaction->total_amount,
                        'payment_method_id' => $transaction->payment_method_id,
                        'received_by' => $transaction->cashier_id,
                        'received_at' => $transaction->processed_at ?? now(),
                        'remarks' => 'Auto-posted from airtime '.$transaction->transaction_number,
                    ]);
                }
            });
    }
}
