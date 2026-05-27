<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AirtimeCommission extends Model
{
    protected $fillable = [
        'transaction_id', 'provider_id', 'branch_id', 'commission_type',
        'commission_value', 'commission_amount', 'computed_by', 'remarks',
    ];

    protected function casts(): array
    {
        return [
            'commission_value' => 'decimal:2',
            'commission_amount' => 'decimal:2',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(AirtimeTransaction::class, 'transaction_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(AirtimeProvider::class, 'provider_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
