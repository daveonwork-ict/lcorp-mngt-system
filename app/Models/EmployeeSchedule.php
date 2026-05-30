<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeSchedule extends Model
{
    protected $fillable = [
        'user_id',
        'branch_id',
        'schedule_date',
        'schedule_type',
        'time_in',
        'time_out',
        'break_start',
        'break_end',
        'is_rest_day',
    ];

    protected function casts(): array
    {
        return [
            'schedule_date' => 'date',
            'is_rest_day' => 'boolean',
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
}
