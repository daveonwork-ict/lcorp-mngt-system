<?php

namespace App\Services;

use App\Models\CashOpening;

class CashOpeningService
{
    public function __construct(
        private readonly BranchAccessService $branchAccessService,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    public function paginate(array $filters = [])
    {
        $user = auth()->user();

        return CashOpening::query()
            ->with(['branch', 'cashier'])
            ->when(! $user || $user->role?->code !== config('rms.owner_role_code'), function ($query) use ($user): void {
                $ids = $user ? $this->branchAccessService->accessibleBranches($user)->pluck('id')->all() : [];
                $query->whereIn('branch_id', $ids ?: [-1]);
            })
            ->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->when($filters['date'] ?? null, fn ($q, $date) => $q->whereDate('opening_date', $date))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
    }

    public function create(array $payload): CashOpening
    {
        $user = auth()->user();

        if (! $user || ! $this->branchAccessService->canAccessBranch($user, (int) $payload['branch_id'])) {
            abort(403, 'Branch access denied.');
        }

        $exists = CashOpening::query()
            ->where('branch_id', $payload['branch_id'])
            ->where('cashier_id', $payload['cashier_id'])
            ->whereDate('opening_date', $payload['opening_date'])
            ->where('status', 'open')
            ->exists();

        if ($exists) {
            abort(422, 'Only one active opening cash is allowed per cashier and branch per day.');
        }

        $opening = CashOpening::query()->create([
            'opening_number' => $payload['opening_number'] ?? ('CO-'.now()->format('YmdHis').'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT)),
            'branch_id' => $payload['branch_id'],
            'cashier_id' => $payload['cashier_id'],
            'opening_date' => $payload['opening_date'],
            'opening_time' => $payload['opening_time'] ?? now()->format('H:i:s'),
            'opening_cash_amount' => $payload['opening_cash_amount'],
            'remarks' => $payload['remarks'] ?? null,
            'status' => 'open',
            'encoded_by' => auth()->id(),
        ]);

        $this->auditLogService->record('finance', 'cash_opening_created', [], $opening->toArray(), $opening->branch_id, 'Opening cash created');

        return $opening;
    }

    public function close(CashOpening $opening): CashOpening
    {
        if ($opening->status !== 'open') {
            abort(422, 'Only open records can be closed.');
        }

        $before = $opening->toArray();
        $opening->update(['status' => 'closed']);
        $this->auditLogService->record('finance', 'cash_opening_closed', $before, $opening->toArray(), $opening->branch_id, 'Opening cash closed');

        return $opening;
    }
}
