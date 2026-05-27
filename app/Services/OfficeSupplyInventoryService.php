<?php

namespace App\Services;

use App\Models\OfficeSupply;
use App\Models\OfficeSupplyInventory;
use App\Models\OfficeSupplyMovement;

class OfficeSupplyInventoryService
{
    public function paginate(array $filters = [])
    {
        return OfficeSupplyInventory::query()
            ->with(['branch', 'supply.category'])
            ->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->when($filters['office_supply_id'] ?? null, fn ($q, $supplyId) => $q->where('office_supply_id', $supplyId))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
    }

    public function stockIn(array $payload): OfficeSupplyInventory
    {
        return $this->applyMovement(
            (int) $payload['branch_id'],
            (int) $payload['office_supply_id'],
            'stock_in',
            (int) $payload['quantity'],
            0,
            $payload['reference_type'] ?? 'manual',
            $payload['reference_id'] ?? null,
            $payload['remarks'] ?? null
        );
    }

    public function issue(array $payload): OfficeSupplyInventory
    {
        return $this->applyMovement(
            (int) $payload['branch_id'],
            (int) $payload['office_supply_id'],
            'issuance',
            0,
            (int) $payload['quantity'],
            $payload['reference_type'] ?? 'office_supply_issuance',
            $payload['reference_id'] ?? null,
            $payload['remarks'] ?? null
        );
    }

    public function applyMovement(int $branchId, int $supplyId, string $type, int $qtyIn, int $qtyOut, ?string $referenceType = null, ?int $referenceId = null, ?string $remarks = null): OfficeSupplyInventory
    {
        $supply = OfficeSupply::query()->findOrFail($supplyId);

        $inventory = OfficeSupplyInventory::query()->firstOrCreate(
            ['branch_id' => $branchId, 'office_supply_id' => $supplyId],
            [
                'quantity_on_hand' => 0,
                'quantity_available' => 0,
                'reorder_level' => $supply->reorder_level,
            ]
        );

        $newOnHand = (int) $inventory->quantity_on_hand + $qtyIn - $qtyOut;
        if ($newOnHand < 0) {
            abort(422, 'Insufficient office supply inventory.');
        }

        $inventory->quantity_on_hand = $newOnHand;
        $inventory->quantity_available = $newOnHand;
        $inventory->reorder_level = $supply->reorder_level;
        $inventory->save();

        OfficeSupplyMovement::query()->create([
            'branch_id' => $branchId,
            'office_supply_id' => $supplyId,
            'movement_type' => $type,
            'quantity_in' => $qtyIn,
            'quantity_out' => $qtyOut,
            'running_balance' => $newOnHand,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'remarks' => $remarks,
            'performed_by' => auth()->id(),
        ]);

        return $inventory;
    }
}
