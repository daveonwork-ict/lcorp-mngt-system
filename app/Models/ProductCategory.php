<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductCategory extends Model
{
    protected $fillable = ['category_code', 'category_name', 'description', 'sort_order', 'status'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }
}
