<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialLedger extends Model
{
    protected $fillable = [
        'branch_id', 'ledger_type', 'reference_type', 'reference_id', 'amount_in', 'amount_out',
        'running_balance', 'description', 'performed_by',
    ];

    protected function casts(): array
    {
        return [
            'amount_in' => 'decimal:2',
            'amount_out' => 'decimal:2',
            'running_balance' => 'decimal:2',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
