<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;

class BranchAccessService
{
    public function canAccessBranch(User $user, ?int $branchId): bool
    {
        if ($branchId === null) {
            return true;
        }

        if ($user->role?->code === config('rms.owner_role_code')) {
            return true;
        }

        return $user->branches()->where('branches.id', $branchId)->exists();
    }

    public function accessibleBranches(User $user): Collection
    {
        if ($user->role?->code === config('rms.owner_role_code')) {
            return \App\Models\Branch::query()->where('is_active', true)->get();
        }

        return $user->branches()->get();
    }
}
