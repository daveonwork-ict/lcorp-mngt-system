<?php

namespace App\Services;

use App\Models\Branch;
use Illuminate\Pagination\LengthAwarePaginator;

class BranchService
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return Branch::query()
            ->with('manager')
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where(function ($sub) use ($search): void {
                $sub->where('name', 'like', "%{$search}%")
                    ->orWhere('branch_name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('branch_code', 'like', "%{$search}%");
            }))
            ->latest('id')
            ->paginate(12)
            ->withQueryString();
    }

    public function create(array $data): Branch
    {
        $data['code'] = $data['branch_code'] ?? $data['code'] ?? null;
        $data['name'] = $data['branch_name'] ?? $data['name'] ?? null;
        $data['is_active'] = ($data['status'] ?? 'active') === 'active';

        $branch = Branch::query()->create($data);

        $this->auditLogService->record('branches', 'branch_created', [], $branch->toArray(), $branch->id, 'Branch created');

        return $branch;
    }

    public function update(Branch $branch, array $data): Branch
    {
        $before = $branch->toArray();
        $data['code'] = $data['branch_code'] ?? $branch->code;
        $data['name'] = $data['branch_name'] ?? $branch->name;
        $data['is_active'] = ($data['status'] ?? $branch->status) === 'active';

        $branch->update($data);

        $this->auditLogService->record('branches', 'branch_updated', $before, $branch->toArray(), $branch->id, 'Branch updated');

        return $branch;
    }

    public function toggleStatus(Branch $branch, string $status): void
    {
        $before = $branch->toArray();

        $branch->update([
            'status' => $status,
            'operational_status' => $status,
            'is_active' => $status === 'active',
        ]);

        $this->auditLogService->record('branches', 'branch_status_changed', $before, $branch->toArray(), $branch->id, 'Branch status changed');
    }

    public function assignUsers(Branch $branch, array $userIds, ?int $primaryUserId = null): void
    {
        $syncData = collect($userIds)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->mapWithKeys(fn ($id) => [$id => ['is_primary' => $primaryUserId === $id]])
            ->toArray();

        $branch->users()->sync($syncData);

        $this->auditLogService->record('branches', 'branch_users_assigned', [], [
            'branch_id' => $branch->id,
            'user_ids' => array_values(array_map('intval', $userIds)),
            'primary_user_id' => $primaryUserId,
        ], $branch->id, 'Branch user assignment changed');
    }
}
