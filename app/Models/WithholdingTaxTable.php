<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WithholdingTaxTable extends Model
{
    protected $fillable = [
        'effective_date',
        'payroll_period_type',
        'taxable_income_from',
        'taxable_income_to',
        'base_tax',
        'excess_over',
        'tax_rate',
    ];

    protected function casts(): array
    {
        return [
            'effective_date' => 'date',
            'taxable_income_from' => 'decimal:2',
            'taxable_income_to' => 'decimal:2',
            'base_tax' => 'decimal:2',
            'excess_over' => 'decimal:2',
            'tax_rate' => 'decimal:4',
        ];
    }
}
