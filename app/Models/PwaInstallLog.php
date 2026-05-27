<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PwaInstallLog extends Model
{
    protected $fillable = [
        'user_id', 'branch_id', 'platform', 'browser', 'device_type',
        'ip_address', 'user_agent', 'installed_at',
    ];

    protected function casts(): array
    {
        return [
            'installed_at' => 'datetime',
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
