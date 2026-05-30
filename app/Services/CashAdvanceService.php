<?php

namespace App\Services;

use App\Models\CashAdvance;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class CashAdvanceService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly BranchAccessService $branchAccessService,
    ) {
    }

    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $user = Auth::user();

        return CashAdvance::query()
            ->with(['user', 'branch', 'approvedBy', 'releasedBy'])
            ->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($user && ! in_array($user->role?->code, [config('rms.owner_role_code'), 'super_admin', 'branch_manager', 'accounting_staff'], true), fn ($q) => $q->where('user_id', $user->id))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();
    }

    public function create(array $data): CashAdvance
    {
        $this->ensureBranchAccess((int) $data['branch_id']);

        $user = Auth::user();

        if ($user && ! in_array($user->role?->code, [config('rms.owner_role_code'), 'super_admin', 'branch_manager', 'accounting_staff'], true)) {
            $data['user_id'] = $user->id;
        }

        $data['status'] = 'pending';
        $data['remaining_balance'] = $data['amount'];

        $advance = CashAdvance::query()->create($data);

        $this->auditLogService->record('hr_cash_advances', 'cash_advance_created', [], $advance->toArray(), $advance->branch_id, 'Cash advance created');

        return $advance;
    }

    public function approve(CashAdvance $advance): CashAdvance
    {
        $this->ensureBranchAccess((int) $advance->branch_id);

        $before = $advance->toArray();
        $advance->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        $this->auditLogService->record('hr_cash_advances', 'cash_advance_approved', $before, $advance->toArray(), $advance->branch_id, 'Cash advance approved');

        return $advance;
    }

    public function release(CashAdvance $advance): CashAdvance
    {
        $this->ensureBranchAccess((int) $advance->branch_id);

        $before = $advance->toArray();
        $advance->update([
            'status' => 'released',
            'released_by' => Auth::id(),
            'released_at' => now(),
        ]);

        $this->auditLogService->record('hr_cash_advances', 'cash_advance_released', $before, $advance->toArray(), $advance->branch_id, 'Cash advance released');

        return $advance;
    }

    private function ensureBranchAccess(int $branchId): void
    {
        $user = Auth::user();

        if ($user && ! $this->branchAccessService->canAccessBranch($user, $branchId)) {
            abort(403, 'Branch cash advance access denied.');
        }
    }
}
