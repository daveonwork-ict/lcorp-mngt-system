<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfficeSupplyMovement extends Model
{
    protected $fillable = [
        'branch_id', 'office_supply_id', 'movement_type', 'quantity_in', 'quantity_out',
        'running_balance', 'reference_type', 'reference_id', 'remarks', 'performed_by',
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
