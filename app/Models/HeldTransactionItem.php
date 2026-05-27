<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HeldTransactionItem extends Model
{
    protected $fillable = [
        'held_transaction_id', 'product_id', 'imei_id', 'quantity',
        'selling_price', 'discount_amount', 'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'selling_price' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function heldTransaction(): BelongsTo
    {
        return $this->belongsTo(HeldTransaction::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function imei(): BelongsTo
    {
        return $this->belongsTo(ProductImei::class, 'imei_id');
    }
}
