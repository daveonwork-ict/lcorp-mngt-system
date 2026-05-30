<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeProfile extends Model
{
    protected $fillable = [
        'user_id',
        'branch_id',
        'position_id',
        'birthdate',
        'gender',
        'civil_status',
        'address',
        'emergency_contact_name',
        'emergency_contact_number',
        'employment_date',
        'employment_type',
        'employment_status',
        'salary_type',
        'salary_rate',
    ];

    protected function casts(): array
    {
        return [
            'birthdate' => 'date',
            'employment_date' => 'date',
            'salary_rate' => 'decimal:2',
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

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }
}
