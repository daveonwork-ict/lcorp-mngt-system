<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashVariance extends Model
{
    protected $fillable = [
        'daily_closing_id', 'branch_id', 'cashier_id', 'expected_cash', 'actual_cash',
        'variance_amount', 'variance_type', 'explanation', 'resolution_status', 'resolved_by', 'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'expected_cash' => 'decimal:2',
            'actual_cash' => 'decimal:2',
            'variance_amount' => 'decimal:2',
            'resolved_at' => 'datetime',
        ];
    }

    public function dailyClosing(): BelongsTo
    {
        return $this->belongsTo(DailyClosing::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
