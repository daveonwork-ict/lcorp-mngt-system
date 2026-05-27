<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarrantyReplacement extends Model
{
    protected $fillable = [
        'claim_id', 'old_product_id', 'old_imei_id', 'replacement_product_id',
        'replacement_imei_id', 'replacement_date', 'approved_by', 'remarks',
    ];

    protected function casts(): array
    {
        return [
            'replacement_date' => 'date',
        ];
    }

    public function claim(): BelongsTo
    {
        return $this->belongsTo(WarrantyClaim::class, 'claim_id');
    }

    public function oldProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'old_product_id');
    }

    public function oldImei(): BelongsTo
    {
        return $this->belongsTo(ProductImei::class, 'old_imei_id');
    }

    public function replacementProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'replacement_product_id');
    }

    public function replacementImei(): BelongsTo
    {
        return $this->belongsTo(ProductImei::class, 'replacement_imei_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
