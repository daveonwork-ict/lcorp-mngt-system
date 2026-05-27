<?php

namespace App\Services;

use App\Models\PaymentMethod;
use App\Models\Sale;
use Illuminate\Support\Collection;

class PaymentService
{
    public function __construct(private readonly SalesAuditService $salesAuditService)
    {
    }

    public function validate(float $totalAmount, array $payments, bool $allowPartial = false): array
    {
        if (empty($payments)) {
            abort(422, 'At least one payment entry is required.');
        }

        $normalized = collect($payments)->map(function (array $payment): array {
            $method = PaymentMethod::query()->findOrFail($payment['payment_method_id']);
            $amount = (float) ($payment['amount'] ?? 0);

            if ($amount <= 0) {
                abort(422, 'Payment amount must be greater than zero.');
            }

            if ($method->requires_reference && empty($payment['payment_reference'])) {
                abort(422, $method->payment_method_name.' requires a reference number.');
            }

            return [
                'payment_method_id' => $method->id,
                'payment_reference' => $payment['payment_reference'] ?? null,
                'amount' => round($amount, 2),
                'remarks' => $payment['remarks'] ?? null,
                'method_name' => $method->payment_method_name,
            ];
        });

        $paid = round((float) $normalized->sum('amount'), 2);
        if (! $allowPartial && $paid < $totalAmount) {
            abort(422, 'Payment is less than total amount.');
        }

        return [
            'payments' => $normalized->values()->toArray(),
            'paid_amount' => $paid,
            'change_amount' => max(0, round($paid - $totalAmount, 2)),
            'payment_status' => $paid >= $totalAmount ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid'),
        ];
    }

    public function record(Sale $sale, array $payments): void
    {
        foreach ($payments as $payment) {
            $sale->payments()->create([
                'payment_method_id' => $payment['payment_method_id'],
                'payment_reference' => $payment['payment_reference'],
                'amount' => $payment['amount'],
                'received_by' => auth()->id(),
                'received_at' => now(),
                'payment_status' => 'paid',
                'remarks' => $payment['remarks'] ?? null,
            ]);
        }

        $this->salesAuditService->log('payment_recorded', [], $sale->payments()->get()->toArray(), $sale->branch_id, 'Sale payments recorded');
    }
}
