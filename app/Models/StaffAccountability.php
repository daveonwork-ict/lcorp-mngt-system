<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffAccountability extends Model
{
    protected $fillable = [
        'employee_id', 'branch_id', 'office_supply_id', 'issuance_item_id', 'quantity_issued',
        'date_issued', 'issued_by', 'received_by', 'purpose', 'remarks',
    ];

    protected function casts(): array
    {
        return [
            'date_issued' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function supply(): BelongsTo
    {
        return $this->belongsTo(OfficeSupply::class, 'office_supply_id');
    }

    public function issuanceItem(): BelongsTo
    {
        return $this->belongsTo(OfficeSupplyIssuanceItem::class, 'issuance_item_id');
    }
}
