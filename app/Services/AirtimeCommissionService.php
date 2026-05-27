<?php

namespace App\Services;

use App\Models\AirtimeCommission;
use App\Models\AirtimeProvider;
use App\Models\AirtimeTransaction;

class AirtimeCommissionService
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function compute(AirtimeProvider $provider, float $loadAmount, ?array $override = null): array
    {
        $type = $provider->default_commission_type;
        $value = (float) $provider->default_commission_value;

        if ($override && ! empty($override['commission_type'])) {
            if (! (auth()->user()?->hasPermission('manage_airtime_providers') ?? false)) {
                abort(403, 'Manual commission override is not allowed.');
            }

            $type = $override['commission_type'];
            $value = (float) ($override['commission_value'] ?? 0);
        }

        $amount = match ($type) {
            'fixed' => $value,
            'percentage' => round($loadAmount * ($value / 100), 2),
            default => 0,
        };

        return [
            'commission_type' => $type,
            'commission_value' => $value,
            'commission_amount' => round($amount, 2),
        ];
    }

    public function record(AirtimeTransaction $transaction, array $commission, ?string $remarks = null): AirtimeCommission
    {
        $model = AirtimeCommission::query()->create([
            'transaction_id' => $transaction->id,
            'provider_id' => $transaction->provider_id,
            'branch_id' => $transaction->branch_id,
            'commission_type' => $commission['commission_type'],
            'commission_value' => $commission['commission_value'],
            'commission_amount' => $commission['commission_amount'],
            'computed_by' => auth()->id(),
            'remarks' => $remarks,
        ]);

        $this->auditLogService->record('airtime', 'commission_computed', [], $model->toArray(), $transaction->branch_id, 'Airtime commission computed');

        return $model;
    }
}
