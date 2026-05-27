<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashDenomination extends Model
{
    protected $fillable = [
        'daily_closing_id', 'denomination', 'quantity', 'total_amount',
    ];

    protected function casts(): array
    {
        return [
            'denomination' => 'decimal:2',
            'quantity' => 'integer',
            'total_amount' => 'decimal:2',
        ];
    }

    public function dailyClosing(): BelongsTo
    {
        return $this->belongsTo(DailyClosing::class);
    }
}
