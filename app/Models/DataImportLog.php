<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DataImportLog extends Model
{
    protected $fillable = [
        'import_number', 'module_name', 'file_name', 'file_path', 'total_rows',
        'successful_rows', 'failed_rows', 'status', 'summary_payload',
        'rejected_rows_path', 'imported_by', 'imported_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'summary_payload' => 'array',
            'imported_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function importer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }

    public function errors(): HasMany
    {
        return $this->hasMany(DataImportError::class);
    }
}
