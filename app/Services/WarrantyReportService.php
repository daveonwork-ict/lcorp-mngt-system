<?php

namespace App\Services;

use App\Models\Warranty;
use App\Models\WarrantyClaim;

class WarrantyReportService
{
    public function warranties(array $filters = [])
    {
        return Warranty::query()
            ->with(['branch', 'customer', 'product.brand', 'sale'])
            ->when($filters['date_from'] ?? null, fn ($q, $date) => $q->whereDate('warranty_start_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($q, $date) => $q->whereDate('warranty_start_date', '<=', $date))
            ->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('warranty_status', $status))
            ->when($filters['product_id'] ?? null, fn ($q, $productId) => $q->where('product_id', $productId))
            ->when($filters['brand_id'] ?? null, fn ($q, $brandId) => $q->whereHas('product', fn ($pq) => $pq->where('brand_id', $brandId)))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
    }

    public function claims(array $filters = [])
    {
        return WarrantyClaim::query()
            ->with(['warranty.product.brand', 'customer', 'branch'])
            ->when($filters['date_from'] ?? null, fn ($q, $date) => $q->whereDate('claim_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($q, $date) => $q->whereDate('claim_date', '<=', $date))
            ->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('claim_status', $status))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
    }

    public function dashboard(): array
    {
        return [
            'active_warranties' => Warranty::query()->where('warranty_status', 'active')->count(),
            'expired_warranties' => Warranty::query()->where('warranty_status', 'expired')->count(),
            'pending_claims' => WarrantyClaim::query()->where('claim_status', 'pending')->count(),
            'approved_claims' => WarrantyClaim::query()->where('claim_status', 'approved')->count(),
            'under_repair' => WarrantyClaim::query()->where('claim_status', 'under_repair')->count(),
            'ready_for_release' => WarrantyClaim::query()->where('claim_status', 'ready_for_release')->count(),
            'replaced_items' => WarrantyClaim::query()->where('claim_status', 'replaced')->count(),
            'rejected_claims' => WarrantyClaim::query()->where('claim_status', 'rejected')->count(),
            'recent_claims' => WarrantyClaim::query()->with(['customer', 'warranty.product'])->latest('id')->limit(10)->get(),
            'expiring' => Warranty::query()->with(['customer', 'product'])->where('warranty_status', 'active')->whereDate('warranty_end_date', '<=', now()->addDays(30)->toDateString())->latest('warranty_end_date')->limit(10)->get(),
            'ready_items' => WarrantyClaim::query()->with(['customer', 'warranty.product'])->where('claim_status', 'ready_for_release')->latest('id')->limit(10)->get(),
            'pending_review' => WarrantyClaim::query()->with(['customer', 'warranty.product'])->whereIn('claim_status', ['pending', 'under_review'])->latest('id')->limit(10)->get(),
        ];
    }
}
