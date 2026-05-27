<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplierPayable extends Model
{
    protected $fillable = [
        'payable_number', 'supplier_id', 'branch_id', 'receiving_report_id', 'invoice_number',
        'payable_date', 'due_date', 'total_amount', 'amount_paid', 'balance_amount',
        'payment_status', 'status', 'remarks',
    ];

    protected function casts(): array
    {
        return [
            'payable_date' => 'date',
            'due_date' => 'date',
            'total_amount' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'balance_amount' => 'decimal:2',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function receivingReport(): BelongsTo
    {
        return $this->belongsTo(ReceivingReport::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class, 'payable_id');
    }
}
