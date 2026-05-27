<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AirtimeTransaction extends Model
{
    protected $fillable = [
        'transaction_number', 'branch_id', 'cashier_id', 'provider_id', 'wallet_id',
        'customer_mobile_number', 'load_amount', 'commission_amount', 'total_amount',
        'payment_method_id', 'payment_reference', 'transaction_status', 'remarks',
        'processed_at', 'reversed_at', 'reversal_reason', 'cashflow_prepared', 'cashflow_payload',
    ];

    protected function casts(): array
    {
        return [
            'load_amount' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'processed_at' => 'datetime',
            'reversed_at' => 'datetime',
            'cashflow_prepared' => 'boolean',
            'cashflow_payload' => 'array',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(AirtimeProvider::class, 'provider_id');
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(AirtimeWallet::class, 'wallet_id');
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }
}
