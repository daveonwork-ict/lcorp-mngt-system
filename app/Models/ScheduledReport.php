<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduledReport extends Model
{
    protected $fillable = [
        'user_id',
        'branch_id',
        'report_type',
        'schedule_frequency',
        'filters',
        'delivery_channel',
        'status',
        'last_run_at',
        'next_run_at',
        'last_error',
    ];

    protected function casts(): array
    {
        return [
            'filters' => 'array',
            'last_run_at' => 'datetime',
            'next_run_at' => 'datetime',
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
