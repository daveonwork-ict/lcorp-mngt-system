<?php

namespace App\Services;

use App\Models\Warranty;
use App\Models\WarrantyClaim;
use App\Models\WarrantyClaimAttachment;
use App\Models\WarrantyClaimStatusLog;

class WarrantyClaimService
{
    public function __construct(
        private readonly NotificationService $notificationService,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    public function paginate(array $filters = [])
    {
        return WarrantyClaim::query()
            ->with(['warranty.product', 'customer', 'branch', 'attachments', 'statusLogs'])
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('claim_status', $status))
            ->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
    }

    public function create(array $payload): WarrantyClaim
    {
        $warranty = Warranty::query()->findOrFail($payload['warranty_id']);

        if ($warranty->warranty_status === 'expired' && ! (auth()->user()?->hasPermission('approve_warranty_claim') ?? false)) {
            abort(422, 'Expired warranty claims require special permission.');
        }

        $claim = WarrantyClaim::query()->create([
            'claim_number' => $payload['claim_number'] ?? ('WCL-'.now()->format('YmdHis').'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT)),
            'warranty_id' => $warranty->id,
            'customer_id' => $warranty->customer_id,
            'branch_id' => $warranty->branch_id,
            'claim_date' => $payload['claim_date'],
            'issue_description' => $payload['issue_description'],
            'product_condition' => $payload['product_condition'] ?? null,
            'claim_status' => 'pending',
            'created_by' => auth()->id(),
        ]);

        WarrantyClaimStatusLog::query()->create([
            'claim_id' => $claim->id,
            'status' => 'pending',
            'remarks' => 'Claim created',
            'updated_by' => auth()->id(),
        ]);

        $warranty->update(['warranty_status' => 'claimed']);

        $this->notificationService->create(null, $claim->branch_id, 'New warranty claim', 'Warranty claim '.$claim->claim_number.' submitted.', 'warranty', ['claim_id' => $claim->id]);
        $this->auditLogService->record('warranty', 'warranty_claim_created', [], $claim->toArray(), $claim->branch_id, 'Warranty claim created');

        return $claim;
    }

    public function addAttachment(WarrantyClaim $claim, array $payload): WarrantyClaimAttachment
    {
        $attachment = WarrantyClaimAttachment::query()->create([
            'claim_id' => $claim->id,
            'file_name' => $payload['file_name'],
            'file_path' => $payload['file_path'],
            'file_type' => $payload['file_type'],
            'file_size' => $payload['file_size'],
            'uploaded_by' => auth()->id(),
        ]);

        $this->auditLogService->record('warranty', 'warranty_claim_attachment_uploaded', [], $attachment->toArray(), $claim->branch_id, 'Warranty claim attachment uploaded');

        return $attachment;
    }

    public function updateStatus(WarrantyClaim $claim, string $status, ?string $remarks = null): WarrantyClaim
    {
        $before = $claim->toArray();

        $claim->update([
            'claim_status' => $status,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'resolution_notes' => $remarks ?: $claim->resolution_notes,
        ]);

        WarrantyClaimStatusLog::query()->create([
            'claim_id' => $claim->id,
            'status' => $status,
            'remarks' => $remarks,
            'updated_by' => auth()->id(),
        ]);

        if ($status === 'rejected') {
            $claim->warranty?->update(['warranty_status' => 'rejected']);
            $this->notificationService->create($claim->customer_id, $claim->branch_id, 'Warranty claim rejected', 'Claim '.$claim->claim_number.' was rejected.', 'warranty', ['claim_id' => $claim->id]);
        }

        if (in_array($status, ['approved', 'under_repair', 'ready_for_release', 'released', 'replaced'], true)) {
            $claim->warranty?->update(['warranty_status' => str_replace('_', ' ', $status)]);
            $this->notificationService->create($claim->customer_id, $claim->branch_id, 'Warranty claim update', 'Claim '.$claim->claim_number.' status: '.$status, 'warranty', ['claim_id' => $claim->id]);
        }

        $this->auditLogService->record('warranty', 'warranty_claim_status_updated', $before, $claim->toArray(), $claim->branch_id, 'Warranty claim status updated');

        return $claim;
    }
}
