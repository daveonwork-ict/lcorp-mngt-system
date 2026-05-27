<?php

namespace App\Services;

use App\Models\SupplierPayable;
use App\Models\SupplierPayment;
use Illuminate\Support\Facades\DB;

class SupplierPaymentService
{
    public function __construct(
        private readonly SupplierPayableService $payableService,
        private readonly PurchasingFinanceIntegrationService $financeIntegration,
        private readonly AuditLogService $auditLogService,
        private readonly NotificationService $notificationService,
    ) {
    }

    public function paginate(array $filters = [])
    {
        return SupplierPayment::query()
            ->with(['supplier', 'payable', 'branch', 'paymentMethod', 'payer'])
            ->when($filters['supplier_id'] ?? null, fn ($q, $supplierId) => $q->where('supplier_id', $supplierId))
            ->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
    }

    public function record(array $payload): SupplierPayment
    {
        return DB::transaction(function () use ($payload): SupplierPayment {
            $payable = SupplierPayable::query()->findOrFail($payload['payable_id']);
            if ((float) $payload['amount_paid'] <= 0) {
                abort(422, 'Payment amount must be greater than zero.');
            }
            if ((float) $payload['amount_paid'] > (float) $payable->balance_amount) {
                abort(422, 'Payment amount exceeds payable balance.');
            }

            $payment = SupplierPayment::query()->create([
                'payment_number' => $payload['payment_number'] ?? ('PAY-'.now()->format('YmdHis').'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT)),
                'supplier_id' => $payable->supplier_id,
                'payable_id' => $payable->id,
                'branch_id' => $payable->branch_id,
                'payment_date' => $payload['payment_date'] ?? now()->toDateString(),
                'payment_method_id' => $payload['payment_method_id'] ?? null,
                'reference_number' => $payload['reference_number'] ?? null,
                'amount_paid' => $payload['amount_paid'],
                'proof_file' => $payload['proof_file'] ?? null,
                'remarks' => $payload['remarks'] ?? null,
                'paid_by' => auth()->id(),
            ]);

            $this->payableService->applyPayment($payable, (float) $payment->amount_paid);
            $this->financeIntegration->createCashOutForSupplierPayment($payment);
            $this->financeIntegration->createLedgerForSupplierPayment($payment);

            $this->auditLogService->record('purchasing', 'supplier_payment_recorded', [], $payment->toArray(), $payment->branch_id, 'Supplier payment recorded');
            $this->notificationService->create(null, $payment->branch_id, 'Supplier payment posted', 'Supplier payment '.$payment->payment_number.' posted.', 'purchasing', ['supplier_payment_id' => $payment->id]);

            return $payment->fresh(['supplier', 'payable']);
        });
    }
}
