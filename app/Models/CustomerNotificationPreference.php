<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerNotificationPreference extends Model
{
    protected $fillable = [
        'customer_id', 'notify_warranty_expiry', 'notify_claim_updates', 'notify_promotions',
    ];

    protected function casts(): array
    {
        return [
            'notify_warranty_expiry' => 'boolean',
            'notify_claim_updates' => 'boolean',
            'notify_promotions' => 'boolean',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
