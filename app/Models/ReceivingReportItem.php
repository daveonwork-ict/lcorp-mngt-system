<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReceivingReportItem extends Model
{
    protected $fillable = [
        'receiving_report_id', 'product_id', 'quantity_received', 'unit_cost', 'subtotal', 'serialized_entries', 'remarks',
    ];

    protected function casts(): array
    {
        return [
            'unit_cost' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'serialized_entries' => 'array',
        ];
    }

    public function receivingReport(): BelongsTo
    {
        return $this->belongsTo(ReceivingReport::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
