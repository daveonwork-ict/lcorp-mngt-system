<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemAcceptanceRecord extends Model
{
    protected $fillable = [
        'acceptance_number', 'branch_id', 'prepared_by', 'accepted_by',
        'acceptance_date', 'criteria_payload', 'status', 'remarks',
    ];

    protected function casts(): array
    {
        return [
            'acceptance_date' => 'date',
            'criteria_payload' => 'array',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function preparer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function acceptor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_by');
    }
}
