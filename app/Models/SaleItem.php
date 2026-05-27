<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id', 'product_id', 'imei_id', 'quantity', 'cost_price', 'selling_price',
        'discount_amount', 'subtotal', 'item_status', 'warranty_required', 'warranty_status',
    ];

    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'warranty_required' => 'boolean',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function imei(): BelongsTo
    {
        return $this->belongsTo(ProductImei::class, 'imei_id');
    }

    public function warranty(): HasOne
    {
        return $this->hasOne(Warranty::class);
    }
}
