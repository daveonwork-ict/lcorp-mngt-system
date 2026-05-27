<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FileAccessLog extends Model
{
    protected $fillable = [
        'user_id', 'branch_id', 'module_name', 'reference_type', 'reference_id',
        'file_name', 'file_path', 'action_type', 'status', 'ip_address', 'user_agent', 'accessed_at',
    ];

    protected function casts(): array
    {
        return [
            'accessed_at' => 'datetime',
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
