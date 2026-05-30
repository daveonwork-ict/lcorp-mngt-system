<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeLoan extends Model
{
    protected $fillable = [
        'loan_number',
        'user_id',
        'branch_id',
        'loan_type',
        'principal_amount',
        'interest_rate',
        'installment_amount',
        'term_months',
        'remaining_balance',
        'start_date',
        'maturity_date',
        'status',
        'approved_by',
        'approved_at',
        'released_by',
        'released_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'maturity_date' => 'date',
            'approved_at' => 'datetime',
            'released_at' => 'datetime',
            'principal_amount' => 'decimal:2',
            'interest_rate' => 'decimal:4',
            'installment_amount' => 'decimal:2',
            'remaining_balance' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function releasedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'released_by');
    }

    public function installments(): HasMany
    {
        return $this->hasMany(LoanInstallment::class);
    }
}
