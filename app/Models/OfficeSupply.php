<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OfficeSupply extends Model
{
    protected $fillable = [
        'supply_code', 'supply_name', 'category_id', 'unit', 'reorder_level', 'description', 'status',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(OfficeSupplyCategory::class, 'category_id');
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(OfficeSupplyInventory::class, 'office_supply_id');
    }
}
