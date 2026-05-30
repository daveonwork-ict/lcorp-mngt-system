<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OvertimeRequest extends Model
{
    protected $fillable = [
        'user_id',
        'branch_id',
        'overtime_date',
        'hours',
        'reason',
        'status',
        'manager_reviewer_id',
        'manager_reviewed_at',
        'hr_reviewer_id',
        'hr_reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'overtime_date' => 'date',
            'manager_reviewed_at' => 'datetime',
            'hr_reviewed_at' => 'datetime',
            'hours' => 'decimal:2',
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

    public function managerReviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_reviewer_id');
    }

    public function hrReviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hr_reviewer_id');
    }
}
