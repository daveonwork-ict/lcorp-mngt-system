<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackupLog extends Model
{
    protected $fillable = [
        'backup_number', 'backup_type', 'status', 'file_path', 'file_size_mb', 'remarks',
        'started_by', 'started_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'file_size_mb' => 'decimal:2',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function starter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'started_by');
    }
}
