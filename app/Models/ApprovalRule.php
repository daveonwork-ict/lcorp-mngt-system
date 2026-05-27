<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalRule extends Model
{
    protected $fillable = [
        'rule_name', 'module_name', 'transaction_type', 'branch_id', 'role_id',
        'minimum_amount', 'maximum_amount', 'approver_role_id', 'approval_level',
        'requires_owner_approval', 'status',
    ];

    protected function casts(): array
    {
        return [
            'minimum_amount' => 'decimal:2',
            'maximum_amount' => 'decimal:2',
            'requires_owner_approval' => 'boolean',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function approverRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'approver_role_id');
    }
}
