<?php

namespace App\Services;

class DiscountService
{
    public function compute(float $subtotal, ?array $discount, bool $canApplyDiscount): float
    {
        if (! $discount || empty($discount['type'])) {
            return 0;
        }

        if (! $canApplyDiscount) {
            abort(403, 'You are not allowed to apply discounts.');
        }

        $value = (float) ($discount['value'] ?? 0);
        if ($value < 0) {
            abort(422, 'Invalid discount value.');
        }

        $discountAmount = match ($discount['type']) {
            'fixed', 'manual' => $value,
            'percentage' => round($subtotal * ($value / 100), 2),
            default => 0,
        };

        if ($discountAmount > $subtotal) {
            abort(422, 'Discount cannot exceed subtotal.');
        }

        return round($discountAmount, 2);
    }
}
