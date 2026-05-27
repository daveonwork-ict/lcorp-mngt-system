<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AirtimeWalletFunding extends Model
{
    protected $fillable = [
        'funding_number', 'wallet_id', 'branch_id', 'provider_id', 'amount', 'funding_date',
        'payment_method', 'reference_number', 'proof_file', 'status', 'requested_by', 'approved_by',
        'approved_at', 'rejection_reason', 'remarks', 'cashflow_prepared', 'cashflow_payload',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'funding_date' => 'date',
            'approved_at' => 'datetime',
            'cashflow_prepared' => 'boolean',
            'cashflow_payload' => 'array',
        ];
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(AirtimeWallet::class, 'wallet_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(AirtimeProvider::class, 'provider_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
