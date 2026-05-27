<?php

namespace App\Services;

use App\Models\Warranty;
use App\Models\WarrantyClaim;

class WarrantyAnalyticsService
{
    public function summary(array $filters = []): array
    {
        $warranties = Warranty::query();
        $warranties->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId));
        $warranties->when($filters['date_from'] ?? null, fn ($q, $date) => $q->whereDate('warranty_start_date', '>=', $date));
        $warranties->when($filters['date_to'] ?? null, fn ($q, $date) => $q->whereDate('warranty_start_date', '<=', $date));

        $claims = WarrantyClaim::query();
        $claims->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId));
        $claims->when($filters['date_from'] ?? null, fn ($q, $date) => $q->whereDate('claim_date', '>=', $date));
        $claims->when($filters['date_to'] ?? null, fn ($q, $date) => $q->whereDate('claim_date', '<=', $date));

        return [
            'cards' => [
                'pending_claims' => (int) (clone $claims)->whereIn('claim_status', ['pending', 'under_review'])->count(),
                'active_warranties' => (int) (clone $warranties)->where('warranty_status', 'active')->count(),
                'expiring_warranties' => (int) (clone $warranties)->whereDate('warranty_end_date', '<=', now()->addDays(30))->count(),
                'replacement_count' => (int) (clone $claims)->where('claim_status', 'replaced')->count(),
            ],
            'charts' => [
                'claims_trend' => (clone $claims)->selectRaw('claim_date as label, COUNT(*) as value')->groupBy('claim_date')->orderBy('label')->limit(31)->get(),
                'claims_by_branch' => (clone $claims)->selectRaw('branch_id as label, COUNT(*) as value')->groupBy('branch_id')->get(),
                'claims_by_product' => (clone $claims)->selectRaw('warranty_id as label, COUNT(*) as value')->groupBy('warranty_id')->orderByDesc('value')->limit(10)->get(),
            ],
            'tables' => [
                'pending_warranty_claims' => (clone $claims)->with(['customer', 'warranty.product', 'branch'])->whereIn('claim_status', ['pending', 'under_review'])->latest('id')->limit(10)->get(),
            ],
        ];
    }
}
