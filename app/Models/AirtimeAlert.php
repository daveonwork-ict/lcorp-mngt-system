<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AirtimeAlert extends Model
{
    protected $fillable = [
        'branch_id', 'provider_id', 'wallet_id', 'transaction_id', 'alert_type',
        'severity', 'message', 'is_resolved', 'resolved_at', 'payload',
    ];

    protected function casts(): array
    {
        return [
            'is_resolved' => 'boolean',
            'resolved_at' => 'datetime',
            'payload' => 'array',
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

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(AirtimeWallet::class, 'wallet_id');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(AirtimeTransaction::class, 'transaction_id');
    }
}
