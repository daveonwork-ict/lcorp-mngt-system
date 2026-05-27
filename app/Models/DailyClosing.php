<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DailyClosing extends Model
{
    protected $fillable = [
        'closing_number', 'branch_id', 'cashier_id', 'closing_date', 'opening_cash', 'product_sales_cash',
        'airtime_sales_cash', 'other_cash_in', 'total_cash_in', 'total_cash_out', 'expected_cash', 'actual_cash',
        'variance_amount', 'variance_type', 'variance_explanation', 'remarks', 'status', 'submitted_by',
        'reviewed_by', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'closing_date' => 'date',
            'opening_cash' => 'decimal:2',
            'product_sales_cash' => 'decimal:2',
            'airtime_sales_cash' => 'decimal:2',
            'other_cash_in' => 'decimal:2',
            'total_cash_in' => 'decimal:2',
            'total_cash_out' => 'decimal:2',
            'expected_cash' => 'decimal:2',
            'actual_cash' => 'decimal:2',
            'variance_amount' => 'decimal:2',
            'reviewed_at' => 'datetime',
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

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function denominations(): HasMany
    {
        return $this->hasMany(CashDenomination::class);
    }

    public function variance(): HasOne
    {
        return $this->hasOne(CashVariance::class);
    }
}
