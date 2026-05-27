<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDevicePreference extends Model
{
    protected $fillable = [
        'user_id', 'preferences', 'allow_low_stock', 'allow_cash_variance',
        'allow_pending_approval', 'allow_announcement', 'allow_chat_message',
        'allow_low_wallet', 'allow_warranty_update', 'allow_daily_closing',
    ];

    protected function casts(): array
    {
        return [
            'preferences' => 'array',
            'allow_low_stock' => 'boolean',
            'allow_cash_variance' => 'boolean',
            'allow_pending_approval' => 'boolean',
            'allow_announcement' => 'boolean',
            'allow_chat_message' => 'boolean',
            'allow_low_wallet' => 'boolean',
            'allow_warranty_update' => 'boolean',
            'allow_daily_closing' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
