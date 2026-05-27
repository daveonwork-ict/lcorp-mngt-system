<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeploymentLog extends Model
{
    protected $fillable = [
        'deployment_number', 'item_key', 'item_label', 'status', 'remarks',
        'meta_payload', 'checked_by', 'checked_at',
    ];

    protected function casts(): array
    {
        return [
            'meta_payload' => 'array',
            'checked_at' => 'datetime',
        ];
    }

    public function checker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }
}
