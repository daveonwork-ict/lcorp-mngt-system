<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryTransferItem extends Model
{
    protected $fillable = ['inventory_transfer_id', 'product_id', 'quantity', 'imei_id', 'remarks'];

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(InventoryTransfer::class, 'inventory_transfer_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productImei(): BelongsTo
    {
        return $this->belongsTo(ProductImei::class, 'imei_id');
    }
}
