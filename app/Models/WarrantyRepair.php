<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarrantyRepair extends Model
{
    protected $fillable = [
        'claim_id', 'repair_details', 'technician_name', 'repair_start_date',
        'repair_end_date', 'repair_status', 'remarks',
    ];

    protected function casts(): array
    {
        return [
            'repair_start_date' => 'date',
            'repair_end_date' => 'date',
        ];
    }

    public function claim(): BelongsTo
    {
        return $this->belongsTo(WarrantyClaim::class, 'claim_id');
    }
}
