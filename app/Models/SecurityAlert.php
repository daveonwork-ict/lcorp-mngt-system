<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityAlert extends Model
{
    protected $fillable = [
        'user_id', 'branch_id', 'alert_type', 'severity', 'module_name', 'message',
        'context_payload', 'is_resolved', 'resolved_by', 'resolved_at', 'alerted_at',
    ];

    protected function casts(): array
    {
        return [
            'context_payload' => 'array',
            'is_resolved' => 'boolean',
            'resolved_at' => 'datetime',
            'alerted_at' => 'datetime',
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

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
