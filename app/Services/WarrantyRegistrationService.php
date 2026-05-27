<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Warranty;
use Carbon\Carbon;

class WarrantyRegistrationService
{
    public function __construct(
        private readonly WarrantyRuleService $warrantyRuleService,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    public function registerForSale(Sale $sale): void
    {
        $sale->loadMissing('items.product', 'items.imei');

        foreach ($sale->items as $item) {
            $this->registerForSaleItem($sale, $item);
        }
    }

    private function registerForSaleItem(Sale $sale, SaleItem $item): void
    {
        if (Warranty::query()->where('sale_item_id', $item->id)->exists()) {
            return;
        }

        if (! $item->warranty_required || ! $item->product) {
            return;
        }

        if (! $sale->customer_id) {
            return;
        }

        $product = $item->product;
        $rule = $this->warrantyRuleService->resolveRule($product);

        $duration = (int) ($rule?->warranty_duration ?? $product->warranty_duration ?? 0);
        $durationType = strtolower((string) ($rule?->warranty_duration_type ?? $product->warranty_duration_type ?? 'months'));
        $requiresImei = (bool) ($rule?->requires_imei ?? ($product->is_serialized || $product->is_imei_required));

        if ($duration <= 0) {
            return;
        }

        if ($requiresImei && ! $item->imei_id) {
            return;
        }

        $startDate = Carbon::parse($sale->sales_date);
        $endDate = match ($durationType) {
            'days', 'day' => $startDate->copy()->addDays($duration),
            'years', 'year' => $startDate->copy()->addYears($duration),
            default => $startDate->copy()->addMonths($duration),
        };

        $warranty = Warranty::query()->create([
            'warranty_number' => 'WAR-'.now()->format('YmdHis').'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT),
            'sale_id' => $sale->id,
            'sale_item_id' => $item->id,
            'customer_id' => $sale->customer_id,
            'product_id' => $item->product_id,
            'imei_id' => $item->imei_id,
            'branch_id' => $sale->branch_id,
            'warranty_start_date' => $startDate->toDateString(),
            'warranty_end_date' => $endDate->toDateString(),
            'warranty_status' => 'active',
            'coverage_details' => $rule?->warranty_coverage,
            'exclusions' => $rule?->exclusions,
            'created_by' => auth()->id(),
        ]);

        $item->update(['warranty_status' => 'registered']);

        $this->auditLogService->record('warranty', 'warranty_auto_created', [], $warranty->toArray(), $sale->branch_id, 'Warranty auto-registered from POS sale');
    }
}
