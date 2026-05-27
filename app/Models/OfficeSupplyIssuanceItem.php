<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfficeSupplyIssuanceItem extends Model
{
    protected $fillable = [
        'issuance_id', 'office_supply_id', 'quantity_requested', 'quantity_issued', 'remarks',
    ];

    public function issuance(): BelongsTo
    {
        return $this->belongsTo(OfficeSupplyIssuance::class, 'issuance_id');
    }

    public function supply(): BelongsTo
    {
        return $this->belongsTo(OfficeSupply::class, 'office_supply_id');
    }
}
