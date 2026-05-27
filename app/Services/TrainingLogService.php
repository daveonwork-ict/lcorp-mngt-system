<?php

namespace App\Services;

use App\Models\TrainingLog;
use App\Models\User;

class TrainingLogService
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function paginate(User $user)
    {
        $query = TrainingLog::query()->with(['branch', 'facilitator', 'recorder']);

        if ($user->role?->code !== config('rms.owner_role_code')) {
            $branchIds = $user->branches()->pluck('branches.id')->all();
            $query->where(function ($scope) use ($branchIds): void {
                $scope->whereNull('branch_id')->orWhereIn('branch_id', $branchIds ?: [-1]);
            });
        }

        return $query->latest('id')->paginate(20)->withQueryString();
    }

    public function create(array $payload): TrainingLog
    {
        $record = TrainingLog::query()->create($payload + [
            'training_number' => 'TRN-'.now()->format('YmdHis').'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT),
            'recorded_by' => auth()->id(),
        ]);

        $this->auditLogService->record('deployment', 'training_log_created', [], $record->toArray(), $record->branch_id, 'Training log created');

        return $record;
    }
}
