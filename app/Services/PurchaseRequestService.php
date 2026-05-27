<?php

namespace App\Services;

use App\Models\PurchaseRequest;
use Illuminate\Support\Facades\DB;

class PurchaseRequestService
{
    public function __construct(
        private readonly BranchAccessService $branchAccessService,
        private readonly AuditLogService $auditLogService,
        private readonly NotificationService $notificationService,
    ) {
    }

    public function paginate(array $filters = [])
    {
        $branchIds = $this->allowedBranchIds();

        return PurchaseRequest::query()
            ->with(['branch', 'requester', 'approver'])
            ->when($branchIds !== null, fn ($q) => $q->whereIn('branch_id', $branchIds))
            ->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
    }

    public function create(array $payload): PurchaseRequest
    {
        return DB::transaction(function () use ($payload): PurchaseRequest {
            $request = PurchaseRequest::query()->create([
                'request_number' => $payload['request_number'] ?? ('PR-'.now()->format('YmdHis').'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT)),
                'branch_id' => $payload['branch_id'],
                'requested_by' => auth()->id(),
                'request_date' => $payload['request_date'] ?? now()->toDateString(),
                'purpose' => $payload['purpose'],
                'priority' => $payload['priority'] ?? 'normal',
                'status' => 'pending_approval',
                'remarks' => $payload['remarks'] ?? null,
            ]);

            foreach ($payload['items'] as $item) {
                $request->items()->create([
                    'product_id' => $item['product_id'],
                    'requested_quantity' => $item['requested_quantity'],
                    'estimated_cost' => $item['estimated_cost'] ?? null,
                    'remarks' => $item['remarks'] ?? null,
                ]);
            }

            $this->auditLogService->record('purchasing', 'purchase_request_created', [], $request->toArray(), $request->branch_id, 'Purchase request submitted');
            $this->notificationService->create(null, $request->branch_id, 'Purchase request submitted', 'Purchase request '.$request->request_number.' was submitted.', 'purchasing', ['purchase_request_id' => $request->id]);

            return $request->fresh(['items']);
        });
    }

    public function approve(PurchaseRequest $request): PurchaseRequest
    {
        if (! in_array($request->status, ['pending_approval', 'draft'], true)) {
            abort(422, 'Purchase request is not pending approval.');
        }

        $request->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $this->auditLogService->record('purchasing', 'purchase_request_approved', [], $request->toArray(), $request->branch_id, 'Purchase request approved');

        return $request->fresh();
    }

    public function reject(PurchaseRequest $request, string $reason): PurchaseRequest
    {
        if (! in_array($request->status, ['pending_approval', 'draft'], true)) {
            abort(422, 'Purchase request is not pending approval.');
        }

        $request->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'rejected_at' => now(),
        ]);

        $this->auditLogService->record('purchasing', 'purchase_request_rejected', [], $request->toArray(), $request->branch_id, 'Purchase request rejected');

        return $request;
    }

    private function allowedBranchIds(): ?array
    {
        $user = auth()->user();
        if (! $user || $user->role?->code === config('rms.owner_role_code')) {
            return null;
        }

        $ids = $this->branchAccessService->accessibleBranches($user)->pluck('id')->all();

        return $ids ?: [-1];
    }
}
