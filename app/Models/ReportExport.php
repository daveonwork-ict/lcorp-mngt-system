<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportExport extends Model
{
    protected $fillable = [
        'user_id',
        'branch_id',
        'report_type',
        'export_format',
        'file_name',
        'filters_used',
        'status',
        'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'filters_used' => 'array',
            'generated_at' => 'datetime',
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
