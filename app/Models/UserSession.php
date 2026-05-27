<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSession extends Model
{
    protected $fillable = [
        'session_id', 'user_id', 'ip_address', 'user_agent', 'device_information',
        'last_activity_at', 'terminated_at', 'terminated_by', 'status',
    ];

    protected function casts(): array
    {
        return [
            'last_activity_at' => 'datetime',
            'terminated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function terminator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'terminated_by');
    }
}
