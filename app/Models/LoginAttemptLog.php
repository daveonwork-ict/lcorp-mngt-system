<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginAttemptLog extends Model
{
    protected $fillable = [
        'user_id', 'login_identifier', 'status', 'ip_address', 'user_agent',
        'device_information', 'logged_at', 'remarks',
    ];

    protected function casts(): array
    {
        return [
            'logged_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
