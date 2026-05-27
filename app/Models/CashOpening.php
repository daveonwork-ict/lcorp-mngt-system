<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashOpening extends Model
{
    protected $fillable = [
        'opening_number', 'branch_id', 'cashier_id', 'opening_date', 'opening_time',
        'opening_cash_amount', 'remarks', 'status', 'encoded_by',
    ];

    protected function casts(): array
    {
        return [
            'opening_date' => 'date',
            'opening_time' => 'datetime:H:i:s',
            'opening_cash_amount' => 'decimal:2',
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

    public function encoder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'encoded_by');
    }
}
