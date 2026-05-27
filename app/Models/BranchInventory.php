<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchInventory extends Model
{
    protected $fillable = [
        'branch_id', 'product_id', 'quantity_on_hand', 'quantity_reserved', 'quantity_available',
        'average_cost', 'inventory_value', 'reorder_level',
    ];

    protected function casts(): array
    {
        return [
            'average_cost' => 'decimal:2',
            'inventory_value' => 'decimal:2',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
