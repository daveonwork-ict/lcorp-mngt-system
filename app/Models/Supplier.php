<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $fillable = [
        'supplier_code', 'supplier_name', 'contact_person', 'contact_number', 'email',
        'address', 'product_categories', 'payment_terms', 'status', 'remarks',
    ];

    protected function casts(): array
    {
        return [
            'product_categories' => 'array',
        ];
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function payables(): HasMany
    {
        return $this->hasMany(SupplierPayable::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class);
    }
}
