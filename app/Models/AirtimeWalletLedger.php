<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AirtimeWalletLedger extends Model
{
    protected $fillable = [
        'wallet_id', 'branch_id', 'provider_id', 'movement_type', 'amount_in', 'amount_out',
        'running_balance', 'reference_type', 'reference_id', 'remarks', 'performed_by',
    ];

    protected function casts(): array
    {
        return [
            'amount_in' => 'decimal:2',
            'amount_out' => 'decimal:2',
            'running_balance' => 'decimal:2',
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
