<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataImportError extends Model
{
    protected $fillable = [
        'data_import_log_id', 'row_number', 'row_payload', 'error_messages',
    ];

    protected function casts(): array
    {
        return [
            'row_payload' => 'array',
            'error_messages' => 'array',
        ];
    }

    public function importLog(): BelongsTo
    {
        return $this->belongsTo(DataImportLog::class, 'data_import_log_id');
    }
}
