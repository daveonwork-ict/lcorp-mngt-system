<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsSnapshot extends Model
{
    protected $fillable = [
        'snapshot_key',
        'branch_id',
        'snapshot_date',
        'metrics',
    ];

    protected function casts(): array
    {
        return [
            'snapshot_date' => 'date',
            'metrics' => 'array',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
