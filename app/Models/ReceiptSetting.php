<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReceiptSetting extends Model
{
    protected $fillable = [
        'branch_id', 'store_name', 'thank_you_message', 'warranty_note', 'show_qr', 'show_branch_address',
    ];

    protected function casts(): array
    {
        return [
            'show_qr' => 'boolean',
            'show_branch_address' => 'boolean',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
