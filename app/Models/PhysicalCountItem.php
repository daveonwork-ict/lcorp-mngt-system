<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhysicalCountItem extends Model
{
    protected $fillable = ['physical_count_id', 'product_id', 'system_quantity', 'counted_quantity', 'variance', 'encoded_imei', 'remarks'];

    public function physicalCount(): BelongsTo
    {
        return $this->belongsTo(PhysicalCount::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
