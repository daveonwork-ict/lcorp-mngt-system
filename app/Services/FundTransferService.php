<?php

namespace App\Services;

use App\Models\FundTransfer;

class FundTransferService
{
    public function __construct(
        private readonly CashInService $cashInService,
        private readonly CashOutService $cashOutService,
        private readonly NotificationService $notificationService,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    public function paginate(array $filters = [])
    {
        return FundTransfer::query()
            ->with(['sourceBranch', 'destinationBranch', 'requester', 'approver'])
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['branch_id'] ?? null, function ($q, $branchId): void {
                $q->where('source_branch_id', $branchId)->orWhere('destination_branch_id', $branchId);
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
    }

    public function request(array $payload): FundTransfer
    {
        $transfer = FundTransfer::query()->create([
            'transfer_number' => $payload['transfer_number'] ?? ('FT-'.now()->format('YmdHis').'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT)),
            'source_branch_id' => $payload['source_branch_id'] ?? null,
            'destination_branch_id' => $payload['destination_branch_id'] ?? null,
            'amount' => $payload['amount'],
            'transfer_method' => $payload['transfer_method'],
            'reference_number' => $payload['reference_number'] ?? null,
            'proof_file' => $payload['proof_file'] ?? null,
            'status' => 'pending',
            'requested_by' => auth()->id(),
            'remarks' => $payload['remarks'] ?? null,
        ]);

        $this->auditLogService->record('finance', 'fund_transfer_requested', [], $transfer->toArray(), $transfer->source_branch_id, 'Fund transfer requested');
        $this->notificationService->create(null, $transfer->source_branch_id, 'Fund transfer request', 'Transfer '.$transfer->transfer_number.' submitted.', 'finance', ['transfer_id' => $transfer->id]);

        return $transfer;
    }

    public function approve(FundTransfer $transfer): FundTransfer
    {
        if ($transfer->status !== 'pending') {
            abort(422, 'Only pending transfers can be approved.');
        }

        $before = $transfer->toArray();

        $transfer->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        if ($transfer->source_branch_id) {
            $this->cashOutService->record([
                'branch_id' => $transfer->source_branch_id,
                'source_type' => 'fund_transfer_out',
                'source_reference_type' => 'fund_transfer',
                'source_reference_id' => $transfer->id,
                'amount' => $transfer->amount,
                'released_by' => auth()->id(),
                'released_at' => now(),
                'remarks' => 'Fund transfer out '.$transfer->transfer_number,
            ]);
        }

        if ($transfer->destination_branch_id) {
            $this->cashInService->record([
                'branch_id' => $transfer->destination_branch_id,
                'source_type' => 'fund_transfer_received',
                'source_reference_type' => 'fund_transfer',
                'source_reference_id' => $transfer->id,
                'amount' => $transfer->amount,
                'received_by' => auth()->id(),
                'received_at' => now(),
                'remarks' => 'Fund transfer received '.$transfer->transfer_number,
            ]);
        }

        $this->auditLogService->record('finance', 'fund_transfer_approved', $before, $transfer->toArray(), $transfer->source_branch_id, 'Fund transfer approved');

        return $transfer;
    }

    public function reject(FundTransfer $transfer, string $reason): FundTransfer
    {
        if ($transfer->status !== 'pending') {
            abort(422, 'Only pending transfers can be rejected.');
        }

        $before = $transfer->toArray();

        $transfer->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);

        $this->auditLogService->record('finance', 'fund_transfer_rejected', $before, $transfer->toArray(), $transfer->source_branch_id, 'Fund transfer rejected');

        return $transfer;
    }
}
