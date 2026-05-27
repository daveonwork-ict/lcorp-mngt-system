<?php

namespace App\Services;

use App\Models\OfficeSupplyIssuance;
use App\Models\StaffAccountability;
use Illuminate\Support\Facades\DB;

class OfficeSupplyIssuanceService
{
    public function __construct(
        private readonly OfficeSupplyInventoryService $inventoryService,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    public function paginate(array $filters = [])
    {
        return OfficeSupplyIssuance::query()
            ->with(['branch', 'requester', 'recipient', 'approver', 'issuer'])
            ->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
    }

    public function create(array $payload): OfficeSupplyIssuance
    {
        return DB::transaction(function () use ($payload): OfficeSupplyIssuance {
            $issuance = OfficeSupplyIssuance::query()->create([
                'issuance_number' => $payload['issuance_number'] ?? ('OSI-'.now()->format('YmdHis').'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT)),
                'branch_id' => $payload['branch_id'],
                'requested_by' => auth()->id(),
                'issued_to' => $payload['issued_to'],
                'issue_date' => $payload['issue_date'] ?? now()->toDateString(),
                'purpose' => $payload['purpose'],
                'status' => 'pending',
                'remarks' => $payload['remarks'] ?? null,
            ]);

            foreach ($payload['items'] as $item) {
                $issuance->items()->create([
                    'office_supply_id' => $item['office_supply_id'],
                    'quantity_requested' => $item['quantity_requested'],
                    'quantity_issued' => 0,
                    'remarks' => $item['remarks'] ?? null,
                ]);
            }

            $this->auditLogService->record('purchasing', 'office_supply_issuance_created', [], $issuance->toArray(), $issuance->branch_id, 'Office supply issuance request created');

            return $issuance->fresh(['items']);
        });
    }

    public function approve(OfficeSupplyIssuance $issuance): OfficeSupplyIssuance
    {
        if ($issuance->status !== 'pending') {
            abort(422, 'Only pending issuance can be approved.');
        }

        $issuance->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return $issuance;
    }

    public function reject(OfficeSupplyIssuance $issuance, string $reason): OfficeSupplyIssuance
    {
        if ($issuance->status !== 'pending') {
            abort(422, 'Only pending issuance can be rejected.');
        }

        $issuance->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
        ]);

        return $issuance;
    }

    public function issue(OfficeSupplyIssuance $issuance): OfficeSupplyIssuance
    {
        return DB::transaction(function () use ($issuance): OfficeSupplyIssuance {
            if (! in_array($issuance->status, ['approved', 'issued'], true)) {
                abort(422, 'Issuance must be approved before release.');
            }

            foreach ($issuance->items as $item) {
                $qty = (int) $item->quantity_requested;
                $this->inventoryService->issue([
                    'branch_id' => $issuance->branch_id,
                    'office_supply_id' => $item->office_supply_id,
                    'quantity' => $qty,
                    'reference_type' => 'office_supply_issuance',
                    'reference_id' => $issuance->id,
                    'remarks' => 'Office supply issued to employee',
                ]);

                $item->update(['quantity_issued' => $qty]);

                StaffAccountability::query()->create([
                    'employee_id' => $issuance->issued_to,
                    'branch_id' => $issuance->branch_id,
                    'office_supply_id' => $item->office_supply_id,
                    'issuance_item_id' => $item->id,
                    'quantity_issued' => $qty,
                    'date_issued' => $issuance->issue_date,
                    'issued_by' => auth()->id(),
                    'received_by' => $issuance->issued_to,
                    'purpose' => $issuance->purpose,
                    'remarks' => $item->remarks,
                ]);
            }

            $issuance->update([
                'status' => 'issued',
                'issued_by' => auth()->id(),
                'issued_at' => now(),
            ]);

            $this->auditLogService->record('purchasing', 'office_supply_issued', [], $issuance->toArray(), $issuance->branch_id, 'Office supply issued');

            return $issuance->fresh(['items']);
        });
    }
}
