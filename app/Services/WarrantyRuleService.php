<?php

namespace App\Services;

use App\Models\Product;
use App\Models\WarrantyRule;

class WarrantyRuleService
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function paginate()
    {
        return WarrantyRule::query()
            ->with(['category', 'brand', 'product'])
            ->latest('id')
            ->paginate(20);
    }

    public function create(array $payload): WarrantyRule
    {
        $rule = WarrantyRule::query()->create($payload + [
            'rule_code' => $payload['rule_code'] ?? ('WR-'.now()->format('YmdHis').'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT)),
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        $this->auditLogService->record('warranty', 'warranty_rule_created', [], $rule->toArray(), null, 'Warranty rule created');

        return $rule;
    }

    public function update(WarrantyRule $rule, array $payload): WarrantyRule
    {
        $before = $rule->toArray();
        $rule->update($payload + ['updated_by' => auth()->id()]);
        $this->auditLogService->record('warranty', 'warranty_rule_updated', $before, $rule->toArray(), null, 'Warranty rule updated');

        return $rule;
    }

    public function resolveRule(Product $product): ?WarrantyRule
    {
        return WarrantyRule::query()
            ->where('status', 'active')
            ->where(function ($q) use ($product): void {
                $q->where('product_id', $product->id)
                    ->orWhere(function ($brandQuery) use ($product): void {
                        $brandQuery->whereNull('product_id')
                            ->where('brand_id', $product->brand_id);
                    })
                    ->orWhere(function ($categoryQuery) use ($product): void {
                        $categoryQuery->whereNull('product_id')
                            ->whereNull('brand_id')
                            ->where('product_category_id', $product->category_id);
                    });
            })
            ->orderByRaw('CASE WHEN product_id IS NOT NULL THEN 1 WHEN brand_id IS NOT NULL THEN 2 ELSE 3 END')
            ->first();
    }
}
