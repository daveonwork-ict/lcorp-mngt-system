<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarrantyRule extends Model
{
    protected $fillable = [
        'rule_code', 'rule_name', 'product_category_id', 'brand_id', 'product_id',
        'warranty_duration', 'warranty_duration_type', 'warranty_coverage', 'exclusions',
        'requires_imei', 'status', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'requires_imei' => 'boolean',
            'warranty_duration' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
