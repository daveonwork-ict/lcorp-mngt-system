<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OfficeSupplyCategory extends Model
{
    protected $fillable = [
        'category_code', 'category_name', 'description', 'status',
    ];

    public function supplies(): HasMany
    {
        return $this->hasMany(OfficeSupply::class, 'category_id');
    }
}
