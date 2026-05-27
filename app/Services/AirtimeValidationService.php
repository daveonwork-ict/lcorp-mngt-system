<?php

namespace App\Services;

use App\Models\AirtimeTransaction;

class AirtimeValidationService
{
    public function validateMobileNumber(string $mobile): string
    {
        $normalized = preg_replace('/\D+/', '', $mobile) ?? '';

        if (str_starts_with($normalized, '63')) {
            $normalized = '0'.substr($normalized, 2);
        }

        if (! preg_match('/^09\d{9}$/', $normalized)) {
            abort(422, 'Invalid mobile number format.');
        }

        return $normalized;
    }

    public function suspiciousReasons(int $branchId, int $providerId, string $mobile, float $amount, ?string $reference = null): array
    {
        $windowMinutes = (int) config('rms.airtime.suspicious_window_minutes', 5);
        $highValueThreshold = (float) config('rms.airtime.high_value_threshold', 1000);

        $repeated = AirtimeTransaction::query()
            ->where('branch_id', $branchId)
            ->where('provider_id', $providerId)
            ->where('customer_mobile_number', $mobile)
            ->where('load_amount', $amount)
            ->where('processed_at', '>=', now()->subMinutes($windowMinutes))
            ->whereIn('transaction_status', ['successful', 'pending'])
            ->exists();

        $duplicateReference = false;
        if ($reference) {
            $duplicateReference = AirtimeTransaction::query()
                ->where('branch_id', $branchId)
                ->where('payment_reference', $reference)
                ->whereNotNull('payment_reference')
                ->exists();
        }

        $reasons = [];
        if ($repeated) {
            $reasons[] = 'Repeated transaction within '.$windowMinutes.' minutes';
        }
        if ($amount >= $highValueThreshold) {
            $reasons[] = 'High-value transaction';
        }
        if ($duplicateReference) {
            $reasons[] = 'Duplicate payment reference';
        }

        return $reasons;
    }
}
