<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollRun extends Model
{
    protected $fillable = [
        'payroll_period_id',
        'branch_id',
        'status',
        'total_gross_pay',
        'total_deductions',
        'total_net_pay',
        'generated_by',
        'hr_reviewed_by',
        'hr_reviewed_at',
        'manager_approved_by',
        'manager_approved_at',
        'owner_approved_by',
        'owner_approved_at',
        'released_by',
        'released_at',
    ];

    protected function casts(): array
    {
        return [
            'hr_reviewed_at' => 'datetime',
            'manager_approved_at' => 'datetime',
            'owner_approved_at' => 'datetime',
            'released_at' => 'datetime',
            'total_gross_pay' => 'decimal:2',
            'total_deductions' => 'decimal:2',
            'total_net_pay' => 'decimal:2',
        ];
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class, 'payroll_period_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PayrollItem::class);
    }
}
