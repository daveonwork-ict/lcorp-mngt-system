<?php

namespace App\Services;

use App\Models\OfficeSupply;
use App\Models\OfficeSupplyCategory;

class OfficeSupplyService
{
    public function categoryList(array $filters = [])
    {
        return OfficeSupplyCategory::query()
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->orderBy('category_name')
            ->paginate(20)
            ->withQueryString();
    }

    public function createCategory(array $payload): OfficeSupplyCategory
    {
        return OfficeSupplyCategory::query()->create([
            'category_code' => $payload['category_code'] ?? ('OSC-'.now()->format('YmdHis').'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT)),
            'category_name' => $payload['category_name'],
            'description' => $payload['description'] ?? null,
            'status' => $payload['status'] ?? 'active',
        ]);
    }

    public function updateCategory(OfficeSupplyCategory $category, array $payload): OfficeSupplyCategory
    {
        $category->update([
            'category_name' => $payload['category_name'],
            'description' => $payload['description'] ?? null,
            'status' => $payload['status'] ?? $category->status,
        ]);

        return $category->fresh();
    }

    public function supplyList(array $filters = [])
    {
        return OfficeSupply::query()
            ->with('category')
            ->when($filters['category_id'] ?? null, fn ($q, $categoryId) => $q->where('category_id', $categoryId))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->orderBy('supply_name')
            ->paginate(20)
            ->withQueryString();
    }

    public function createSupply(array $payload): OfficeSupply
    {
        return OfficeSupply::query()->create([
            'supply_code' => $payload['supply_code'] ?? ('OS-'.now()->format('YmdHis').'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT)),
            'supply_name' => $payload['supply_name'],
            'category_id' => $payload['category_id'],
            'unit' => $payload['unit'],
            'reorder_level' => $payload['reorder_level'] ?? 0,
            'description' => $payload['description'] ?? null,
            'status' => $payload['status'] ?? 'active',
        ]);
    }

    public function updateSupply(OfficeSupply $supply, array $payload): OfficeSupply
    {
        $supply->update([
            'supply_name' => $payload['supply_name'],
            'category_id' => $payload['category_id'],
            'unit' => $payload['unit'],
            'reorder_level' => $payload['reorder_level'] ?? $supply->reorder_level,
            'description' => $payload['description'] ?? null,
            'status' => $payload['status'] ?? $supply->status,
        ]);

        return $supply->fresh();
    }
}
