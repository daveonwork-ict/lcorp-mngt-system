<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'audit_number', 'user_id', 'branch_id', 'module_name', 'action_type',
        'reference_type', 'reference_id', 'before_value', 'after_value',
        'ip_address', 'user_agent', 'device_information',
    ];

    protected function casts(): array
    {
        return [
            'before_value' => 'array',
            'after_value' => 'array',
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
