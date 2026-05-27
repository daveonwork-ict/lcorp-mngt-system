<?php

namespace App\Services;

use App\Models\Role;
use Illuminate\Pagination\LengthAwarePaginator;

class RoleService
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function paginate(): LengthAwarePaginator
    {
        return Role::query()->withCount('users')->latest('id')->paginate(12);
    }

    public function create(array $data): Role
    {
        $role = Role::query()->create($data);
        $this->auditLogService->record('roles', 'role_created', [], $role->toArray(), null, 'Role created');

        return $role;
    }

    public function update(Role $role, array $data): Role
    {
        $before = $role->toArray();
        $role->update($data);

        $this->auditLogService->record('roles', 'role_updated', $before, $role->toArray(), null, 'Role updated');

        return $role;
    }

    public function toggleStatus(Role $role, string $status): void
    {
        $before = $role->toArray();
        $role->update(['status' => $status]);

        $this->auditLogService->record('roles', 'role_status_changed', $before, $role->toArray(), null, 'Role status changed');
    }

    public function syncPermissions(Role $role, array $permissionIds): void
    {
        $role->permissions()->sync($permissionIds);

        $this->auditLogService->record('roles', 'permission_assigned', [], [
            'role_id' => $role->id,
            'permission_ids' => array_values($permissionIds),
        ], null, 'Role permissions updated');
    }
}
