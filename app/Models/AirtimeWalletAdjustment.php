<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AirtimeWalletAdjustment extends Model
{
    protected $fillable = [
        'adjustment_number', 'wallet_id', 'branch_id', 'provider_id', 'adjustment_type',
        'amount', 'reason', 'status', 'requested_by', 'approved_by', 'approved_at',
        'approval_remarks', 'remarks',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'approved_at' => 'datetime',
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
}
