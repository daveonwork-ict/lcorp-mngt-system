<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhysicalCount extends Model
{
    protected $fillable = [
        'count_number', 'branch_id', 'category_id', 'status', 'created_by', 'reviewed_by', 'submitted_at',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PhysicalCountItem::class);
    }
}
