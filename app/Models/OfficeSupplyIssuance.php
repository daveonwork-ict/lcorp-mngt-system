<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OfficeSupplyIssuance extends Model
{
    protected $fillable = [
        'issuance_number', 'branch_id', 'requested_by', 'issued_to', 'issued_by', 'issue_date', 'purpose',
        'status', 'rejection_reason', 'remarks', 'approved_by', 'approved_at', 'issued_at',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'approved_at' => 'datetime',
            'issued_at' => 'datetime',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_to');
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OfficeSupplyIssuanceItem::class, 'issuance_id');
    }
}
