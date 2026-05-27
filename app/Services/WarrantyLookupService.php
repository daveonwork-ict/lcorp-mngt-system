<?php

namespace App\Services;

use App\Models\Warranty;

class WarrantyLookupService
{
    public function search(array $filters = [])
    {
        return Warranty::query()
            ->with(['sale', 'customer', 'product.brand', 'imei', 'branch', 'claims'])
            ->when($filters['warranty_number'] ?? null, fn ($q, $value) => $q->where('warranty_number', 'like', "%{$value}%"))
            ->when($filters['receipt_number'] ?? null, fn ($q, $value) => $q->whereHas('sale', fn ($saleQ) => $saleQ->where('sales_number', 'like', "%{$value}%")))
            ->when($filters['customer'] ?? null, fn ($q, $value) => $q->whereHas('customer', function ($customerQ) use ($value): void {
                $customerQ->where('full_name', 'like', "%{$value}%")
                    ->orWhere('mobile_number', 'like', "%{$value}%");
            }))
            ->when($filters['imei'] ?? null, fn ($q, $value) => $q->whereHas('imei', fn ($imeiQ) => $imeiQ->where('imei_number', 'like', "%{$value}%")))
            ->when($filters['product'] ?? null, fn ($q, $value) => $q->whereHas('product', fn ($productQ) => $productQ->where('product_name', 'like', "%{$value}%")))
            ->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
    }
}
