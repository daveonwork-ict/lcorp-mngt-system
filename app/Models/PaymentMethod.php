<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentMethod extends Model
{
    protected $fillable = [
        'payment_method_name', 'payment_type', 'requires_reference', 'status',
    ];

    protected function casts(): array
    {
        return [
            'requires_reference' => 'boolean',
        ];
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SalePayment::class);
    }
}
