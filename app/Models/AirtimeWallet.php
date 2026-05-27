<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AirtimeWallet extends Model
{
    protected $fillable = [
        'wallet_number', 'branch_id', 'provider_id', 'beginning_balance',
        'current_balance', 'low_balance_threshold', 'status', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'beginning_balance' => 'decimal:2',
            'current_balance' => 'decimal:2',
            'low_balance_threshold' => 'decimal:2',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(AirtimeProvider::class, 'provider_id');
    }

    public function ledgers(): HasMany
    {
        return $this->hasMany(AirtimeWalletLedger::class, 'wallet_id');
    }

    public function fundings(): HasMany
    {
        return $this->hasMany(AirtimeWalletFunding::class, 'wallet_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(AirtimeTransaction::class, 'wallet_id');
    }
}
