<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warranty extends Model
{
    protected $fillable = [
        'warranty_number', 'sale_id', 'sale_item_id', 'customer_id', 'product_id', 'imei_id',
        'branch_id', 'warranty_start_date', 'warranty_end_date', 'warranty_status',
        'coverage_details', 'exclusions', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'warranty_start_date' => 'date',
            'warranty_end_date' => 'date',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function saleItem(): BelongsTo
    {
        return $this->belongsTo(SaleItem::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function imei(): BelongsTo
    {
        return $this->belongsTo(ProductImei::class, 'imei_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function claims(): HasMany
    {
        return $this->hasMany(WarrantyClaim::class);
    }
}
