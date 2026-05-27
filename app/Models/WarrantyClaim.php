<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WarrantyClaim extends Model
{
    protected $fillable = [
        'claim_number', 'warranty_id', 'customer_id', 'branch_id', 'claim_date',
        'issue_description', 'product_condition', 'claim_status', 'reviewed_by', 'reviewed_at',
        'resolution_type', 'resolution_notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'claim_date' => 'date',
            'reviewed_at' => 'datetime',
        ];
    }

    public function warranty(): BelongsTo
    {
        return $this->belongsTo(Warranty::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(WarrantyClaimAttachment::class, 'claim_id');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(WarrantyClaimStatusLog::class, 'claim_id');
    }

    public function repair(): HasOne
    {
        return $this->hasOne(WarrantyRepair::class, 'claim_id');
    }

    public function replacement(): HasOne
    {
        return $this->hasOne(WarrantyReplacement::class, 'claim_id');
    }
}
