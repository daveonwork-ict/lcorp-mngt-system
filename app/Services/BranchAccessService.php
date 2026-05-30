<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;

class BranchAccessService
{
    public function hasGlobalBranchAccess(User $user): bool
    {
        $roleCode = $user->role?->code;
        if (! $roleCode) {
            return false;
        }

        $globalRoleCodes = collect(config('rms.global_branch_role_codes', []))
            ->filter()
            ->map(static fn ($code) => strtolower((string) $code))
            ->all();

        return in_array(strtolower((string) $roleCode), $globalRoleCodes, true);
    }

    public function canAccessBranch(User $user, ?int $branchId): bool
    {
        if ($branchId === null) {
            return true;
        }

        if ($this->hasGlobalBranchAccess($user)) {
            return true;
        }

        return $user->branches()->where('branches.id', $branchId)->exists();
    }

    public function accessibleBranches(User $user): Collection
    {
        if ($this->hasGlobalBranchAccess($user)) {
            return \App\Models\Branch::query()->where('is_active', true)->get();
        }

        return $user->branches()->get();
    }
}
