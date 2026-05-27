<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportFilter extends Model
{
    protected $fillable = [
        'user_id',
        'filter_name',
        'report_type',
        'filter_payload',
    ];

    protected function casts(): array
    {
        return [
            'filter_payload' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
