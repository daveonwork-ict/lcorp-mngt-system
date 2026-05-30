<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagibigContributionTable extends Model
{
    protected $fillable = [
        'effective_date',
        'salary_from',
        'salary_to',
        'employee_rate',
        'employer_rate',
        'employee_share',
        'employer_share',
    ];

    protected function casts(): array
    {
        return [
            'effective_date' => 'date',
            'salary_from' => 'decimal:2',
            'salary_to' => 'decimal:2',
            'employee_rate' => 'decimal:4',
            'employer_rate' => 'decimal:4',
            'employee_share' => 'decimal:2',
            'employer_share' => 'decimal:2',
        ];
    }
}
