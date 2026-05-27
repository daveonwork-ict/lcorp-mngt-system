<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return User::query()
            ->with(['role', 'primaryBranch'])
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where(function ($sub) use ($search): void {
                $sub->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('employee_code', 'like', "%{$search}%");
            }))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->latest('id')
            ->paginate(12)
            ->withQueryString();
    }

    public function create(array $data): User
    {
        if (isset($data['profile_photo']) && $data['profile_photo'] instanceof UploadedFile) {
            $data['profile_photo'] = $data['profile_photo']->store('profile-photos', 'public');
        }

        $data['full_name'] = $this->buildFullName($data);
        $data['name'] = $data['full_name'] ?: ($data['username'] ?? $data['email']);
        $data['is_active'] = ($data['status'] ?? 'active') === 'active';

        $user = User::query()->create($data);

        $this->syncBranches($user, $data['branch_ids'] ?? [], $data['primary_branch_id'] ?? null, false);

        $this->auditLogService->record('users', 'user_created', [], $user->toArray(), $user->primary_branch_id, 'User created');

        return $user;
    }

    public function update(User $user, array $data): User
    {
        $before = $user->toArray();

        if (isset($data['profile_photo']) && $data['profile_photo'] instanceof UploadedFile) {
            $data['profile_photo'] = $data['profile_photo']->store('profile-photos', 'public');
        }

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $data['full_name'] = $this->buildFullName($data + $user->toArray());
        $data['name'] = $data['full_name'] ?: ($data['username'] ?? $user->name);
        $data['is_active'] = ($data['status'] ?? $user->status) === 'active';

        $user->update($data);

        $this->syncBranches($user, $data['branch_ids'] ?? [], $data['primary_branch_id'] ?? $user->primary_branch_id);

        $this->auditLogService->record('users', 'user_updated', $before, $user->fresh()->toArray(), $user->primary_branch_id, 'User updated');

        return $user->fresh();
    }

    public function toggleStatus(User $user, string $status): void
    {
        $before = $user->toArray();

        $user->update([
            'status' => $status,
            'is_active' => $status === 'active',
        ]);

        $this->auditLogService->record('users', 'user_status_changed', $before, $user->toArray(), $user->primary_branch_id, 'User status changed');
    }

    public function resetPassword(User $user, string $password): void
    {
        $user->update(['password' => Hash::make($password)]);

        $this->auditLogService->record('users', 'password_reset', [], ['user_id' => $user->id], $user->primary_branch_id, 'User password reset');
    }

    public function syncBranches(User $user, array $branchIds, ?int $primaryBranchId, bool $log = true): void
    {
        $branchIds = collect($branchIds)->map(fn ($id) => (int) $id)->filter()->unique()->values();

        if ($primaryBranchId && ! $branchIds->contains($primaryBranchId)) {
            $branchIds->push((int) $primaryBranchId);
        }

        $syncData = $branchIds
            ->mapWithKeys(fn ($id) => [$id => ['is_primary' => (int) $id === (int) $primaryBranchId]])
            ->toArray();

        $user->branches()->sync($syncData);

        $user->update(['primary_branch_id' => $primaryBranchId]);

        if ($log) {
            $this->auditLogService->record('users', 'user_branch_assignment_changed', [], [
                'user_id' => $user->id,
                'branch_ids' => array_values($branchIds->toArray()),
                'primary_branch_id' => $primaryBranchId,
            ], $primaryBranchId, 'User branch assignment changed');
        }
    }

    private function buildFullName(array $data): string
    {
        return trim(implode(' ', array_filter([
            $data['first_name'] ?? null,
            $data['middle_name'] ?? null,
            $data['last_name'] ?? null,
            $data['suffix'] ?? null,
        ])));
    }
}
