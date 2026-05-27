<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseCategory extends Model
{
    protected $fillable = [
        'category_code', 'category_name', 'description', 'requires_approval', 'receipt_required',
        'monthly_budget_limit', 'status',
    ];

    protected function casts(): array
    {
        return [
            'requires_approval' => 'boolean',
            'receipt_required' => 'boolean',
            'monthly_budget_limit' => 'decimal:2',
        ];
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'category_id');
    }
}
