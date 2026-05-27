<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AirtimeProvider extends Model
{
    protected $fillable = [
        'provider_code', 'provider_name', 'description', 'logo',
        'default_commission_type', 'default_commission_value', 'status',
    ];

    protected function casts(): array
    {
        return [
            'default_commission_value' => 'decimal:2',
        ];
    }

    public function wallets(): HasMany
    {
        return $this->hasMany(AirtimeWallet::class, 'provider_id');
    }
}
