<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfficeSupplyInventory extends Model
{
    protected $fillable = [
        'branch_id', 'office_supply_id', 'quantity_on_hand', 'quantity_available', 'reorder_level',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function supply(): BelongsTo
    {
        return $this->belongsTo(OfficeSupply::class, 'office_supply_id');
    }
}
