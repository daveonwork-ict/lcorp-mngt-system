<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollItem extends Model
{
    protected $fillable = [
        'payroll_run_id',
        'user_id',
        'branch_id',
        'basic_pay',
        'overtime_pay',
        'allowances',
        'holiday_pay',
        'night_differential_pay',
        'incentives',
        'gross_pay',
        'sss_deduction',
        'philhealth_deduction',
        'pagibig_deduction',
        'withholding_tax_deduction',
        'loan_deduction',
        'cash_advance_deduction',
        'other_deduction',
        'total_deductions',
        'net_pay',
        'status',
        'computation_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'computation_snapshot' => 'array',
            'basic_pay' => 'decimal:2',
            'overtime_pay' => 'decimal:2',
            'allowances' => 'decimal:2',
            'holiday_pay' => 'decimal:2',
            'night_differential_pay' => 'decimal:2',
            'incentives' => 'decimal:2',
            'gross_pay' => 'decimal:2',
            'sss_deduction' => 'decimal:2',
            'philhealth_deduction' => 'decimal:2',
            'pagibig_deduction' => 'decimal:2',
            'withholding_tax_deduction' => 'decimal:2',
            'loan_deduction' => 'decimal:2',
            'cash_advance_deduction' => 'decimal:2',
            'other_deduction' => 'decimal:2',
            'total_deductions' => 'decimal:2',
            'net_pay' => 'decimal:2',
        ];
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class, 'payroll_run_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
