<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SssContributionTable extends Model
{
    protected $fillable = [
        'effective_date',
        'salary_from',
        'salary_to',
        'msc',
        'employer_share',
        'employee_share',
    ];

    protected function casts(): array
    {
        return [
            'effective_date' => 'date',
            'salary_from' => 'decimal:2',
            'salary_to' => 'decimal:2',
            'msc' => 'decimal:2',
            'employer_share' => 'decimal:2',
            'employee_share' => 'decimal:2',
        ];
    }
}
