<?php

namespace App\Services;

use App\Models\SystemAcceptanceRecord;
use App\Models\User;

class SystemAcceptanceService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly NotificationService $notificationService,
    ) {
    }

    public function paginate(User $user)
    {
        $query = SystemAcceptanceRecord::query()->with(['branch', 'preparer', 'acceptor']);

        if ($user->role?->code !== config('rms.owner_role_code')) {
            $branchIds = $user->branches()->pluck('branches.id')->all();
            $query->where(function ($scope) use ($branchIds): void {
                $scope->whereNull('branch_id')->orWhereIn('branch_id', $branchIds ?: [-1]);
            });
        }

        return $query->latest('id')->paginate(20)->withQueryString();
    }

    public function create(array $payload, User $user): SystemAcceptanceRecord
    {
        $record = SystemAcceptanceRecord::query()->create([
            'acceptance_number' => 'ACC-'.now()->format('YmdHis').'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT),
            'branch_id' => $payload['branch_id'] ?? $user->primary_branch_id,
            'prepared_by' => $user->id,
            'accepted_by' => $payload['accepted_by'] ?? null,
            'acceptance_date' => $payload['acceptance_date'] ?? now()->toDateString(),
            'criteria_payload' => $payload['criteria_payload'] ?? null,
            'status' => $payload['status'] ?? 'draft',
            'remarks' => $payload['remarks'] ?? null,
        ]);

        $this->auditLogService->record('deployment', 'system_acceptance_created', [], $record->toArray(), $record->branch_id, 'System acceptance record created', $user->id);

        if ($record->status === 'ready_for_acceptance') {
            $this->notificationService->create(null, $record->branch_id, 'Final acceptance ready', 'System acceptance record '.$record->acceptance_number.' is ready for client review.', 'deployment', ['system_acceptance_record_id' => $record->id]);
        }

        return $record;
    }
}
