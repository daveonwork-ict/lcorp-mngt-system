<?php

namespace App\Services;

use App\Models\EmployeeProfile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmployeeProfileService
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return EmployeeProfile::query()
            ->with(['user', 'branch', 'position'])
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->whereHas('user', function ($userQuery) use ($search): void {
                $userQuery->where('full_name', 'like', "%{$search}%")
                    ->orWhere('employee_code', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            }))
            ->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->when($filters['employment_status'] ?? null, fn ($q, $status) => $q->where('employment_status', $status))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();
    }

    public function create(array $data): EmployeeProfile
    {
        $profile = DB::transaction(function () use ($data): EmployeeProfile {
            $data['user_id'] = $this->resolveUserId($data);

            $profile = EmployeeProfile::query()->create($this->profilePayload($data));
            $this->syncUserBranchAccess($profile);

            return $profile;
        });

        $this->auditLogService->record('hr_employees', 'employee_profile_created', [], $profile->toArray(), $profile->branch_id, 'Employee profile created');

        return $profile;
    }

    public function update(EmployeeProfile $profile, array $data): EmployeeProfile
    {
        $before = $profile->toArray();

        DB::transaction(function () use ($profile, $data): void {
            $data['user_id'] = $this->resolveUserId($data, $profile);
            $profile->update($this->profilePayload($data));
            $this->syncUserBranchAccess($profile->fresh());
        });

        $profile->refresh();

        $this->auditLogService->record('hr_employees', 'employee_profile_updated', $before, $profile->toArray(), $profile->branch_id, 'Employee profile updated');

        return $profile;
    }

    private function resolveUserId(array $data, ?EmployeeProfile $profile = null): int
    {
        if (! empty($data['create_user_account'])) {
            $existingUser = $profile?->user;

            if ($existingUser) {
                $this->updateEmployeeUser($existingUser, $data);

                return $existingUser->id;
            }

            return $this->createEmployeeUser($data)->id;
        }

        return (int) ($data['user_id'] ?? $profile?->user_id);
    }

    private function createEmployeeUser(array $data): User
    {
        $roleId = Role::query()->where('code', 'staff_user')->value('id');
        $firstName = trim((string) ($data['account_first_name'] ?? 'Employee'));
        $lastName = trim((string) ($data['account_last_name'] ?? 'User'));
        $fullName = trim($firstName.' '.$lastName);

        return User::query()->create([
            'employee_code' => $data['account_employee_code'] ?: $this->generateEmployeeCode(),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'full_name' => $fullName,
            'name' => $fullName,
            'username' => (string) $data['account_username'],
            'email' => (string) $data['account_email'],
            'mobile_number' => $data['account_mobile_number'] ?? null,
            'role_id' => $roleId,
            'primary_branch_id' => $data['branch_id'] ?? null,
            'status' => ($data['employment_status'] ?? 'active') === 'active' ? 'active' : 'inactive',
            'is_active' => ($data['employment_status'] ?? 'active') === 'active',
            'password' => Hash::make((string) $data['account_password']),
        ]);
    }

    private function updateEmployeeUser(User $user, array $data): void
    {
        $firstName = trim((string) ($data['account_first_name'] ?? $user->first_name ?? ''));
        $lastName = trim((string) ($data['account_last_name'] ?? $user->last_name ?? ''));
        $fullName = trim(($firstName ?: $user->first_name ?: '').' '.($lastName ?: $user->last_name ?: '')) ?: $user->display_name;

        $payload = [
            'employee_code' => $data['account_employee_code'] ?: $user->employee_code ?: $this->generateEmployeeCode(),
            'first_name' => $firstName ?: $user->first_name,
            'last_name' => $lastName ?: $user->last_name,
            'full_name' => $fullName,
            'name' => $fullName,
            'username' => $data['account_username'] ?? $user->username,
            'email' => $data['account_email'] ?? $user->email,
            'mobile_number' => $data['account_mobile_number'] ?? $user->mobile_number,
            'primary_branch_id' => $data['branch_id'] ?? $user->primary_branch_id,
            'status' => ($data['employment_status'] ?? 'active') === 'active' ? 'active' : 'inactive',
            'is_active' => ($data['employment_status'] ?? 'active') === 'active',
        ];

        if (! empty($data['account_password'])) {
            $payload['password'] = Hash::make((string) $data['account_password']);
        }

        $user->update($payload);
    }

    private function syncUserBranchAccess(EmployeeProfile $profile): void
    {
        if (! $profile->user_id || ! $profile->branch_id) {
            return;
        }

        $user = $profile->user;
        if (! $user) {
            return;
        }

        $user->branches()->syncWithoutDetaching([
            $profile->branch_id => ['is_primary' => true],
        ]);

        $user->update(['primary_branch_id' => $profile->branch_id]);
    }

    private function profilePayload(array $data): array
    {
        return collect($data)->except([
            'create_user_account',
            'account_first_name',
            'account_last_name',
            'account_email',
            'account_username',
            'account_mobile_number',
            'account_employee_code',
            'account_password',
            'account_password_confirmation',
        ])->all();
    }

    private function generateEmployeeCode(): string
    {
        return 'EMP-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));
    }
}
