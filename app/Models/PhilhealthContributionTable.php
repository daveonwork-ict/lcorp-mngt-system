<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhilhealthContributionTable extends Model
{
    protected $fillable = [
        'effective_date',
        'salary_from',
        'salary_to',
        'premium_rate',
        'employer_share',
        'employee_share',
    ];

    protected function casts(): array
    {
        return [
            'effective_date' => 'date',
            'salary_from' => 'decimal:2',
            'salary_to' => 'decimal:2',
            'premium_rate' => 'decimal:4',
            'employer_share' => 'decimal:2',
            'employee_share' => 'decimal:2',
        ];
    }
}
