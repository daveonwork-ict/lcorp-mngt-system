<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\AnnouncementTarget;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AnnouncementTargetService
{
    public function syncTargets(Announcement $announcement, array $targets): void
    {
        $normalized = collect($targets)
            ->map(function (array $target): array {
                return [
                    'target_type' => (string) ($target['target_type'] ?? ''),
                    'target_id' => isset($target['target_id']) && $target['target_id'] !== '' ? (int) $target['target_id'] : null,
                ];
            })
            ->filter(fn (array $target): bool => $target['target_type'] !== '')
            ->unique(fn (array $target): string => $target['target_type'].':'.($target['target_id'] ?? 'null'))
            ->values();

        if ($normalized->isEmpty()) {
            $normalized = collect([['target_type' => 'all_users', 'target_id' => null]]);
        }

        $announcement->targets()->delete();
        $announcement->targets()->createMany($normalized->all());
    }

    public function scopeVisibleToUser(Builder $query, User $user): Builder
    {
        if ($user->role?->code === config('rms.owner_role_code')) {
            return $query;
        }

        $branchIds = $user->branches()->pluck('branches.id')->all();
        $roleId = $user->role_id;

        return $query->where(function (Builder $outer) use ($user, $branchIds, $roleId): void {
            $outer->whereHas('targets', function (Builder $targets): void {
                $targets->where('target_type', 'all_users');
            })->orWhereHas('targets', function (Builder $targets) use ($user): void {
                $targets->where('target_type', 'user')->where('target_id', $user->id);
            });

            if ($roleId) {
                $outer->orWhereHas('targets', function (Builder $targets) use ($roleId): void {
                    $targets->where('target_type', 'role')->where('target_id', $roleId);
                });
            }

            if (! empty($branchIds)) {
                $outer->orWhereHas('targets', function (Builder $targets) use ($branchIds): void {
                    $targets->where('target_type', 'branch')->whereIn('target_id', $branchIds);
                });
            }

            $roleCode = $user->role?->code;
            if ($roleCode) {
                $outer->orWhereHas('targets', function (Builder $targets) use ($roleCode): void {
                    $targets->where('target_type', $roleCode.'_only');
                });
            }

            if (in_array($roleCode, ['owner', 'super_admin', 'branch_manager'], true)) {
                $outer->orWhereHas('targets', fn (Builder $targets) => $targets->where('target_type', 'management_only'));
            }

            if ($roleCode === 'cashier') {
                $outer->orWhereHas('targets', fn (Builder $targets) => $targets->where('target_type', 'cashier_only'));
            }

            if ($roleCode === 'inventory_staff') {
                $outer->orWhereHas('targets', fn (Builder $targets) => $targets->where('target_type', 'inventory_staff_only'));
            }

            if ($roleCode === 'accounting_staff') {
                $outer->orWhereHas('targets', fn (Builder $targets) => $targets->where('target_type', 'accounting_only'));
            }
        });
    }

    public function resolveTargetUserIds(Announcement $announcement): Collection
    {
        $targets = $announcement->targets()->get();
        if ($targets->isEmpty() || $targets->contains(fn (AnnouncementTarget $t): bool => $t->target_type === 'all_users')) {
            return User::query()->pluck('id');
        }

        $userIds = collect();

        foreach ($targets as $target) {
            if ($target->target_type === 'user' && $target->target_id) {
                $userIds->push($target->target_id);
                continue;
            }

            if ($target->target_type === 'branch' && $target->target_id) {
                $branchUsers = User::query()->whereHas('branches', fn (Builder $q) => $q->where('branches.id', $target->target_id))->pluck('id');
                $userIds = $userIds->merge($branchUsers);
                continue;
            }

            if ($target->target_type === 'role' && $target->target_id) {
                $roleUsers = User::query()->where('role_id', $target->target_id)->pluck('id');
                $userIds = $userIds->merge($roleUsers);
                continue;
            }

            $roleMap = [
                'management_only' => ['owner', 'super_admin', 'branch_manager'],
                'cashier_only' => ['cashier'],
                'inventory_staff_only' => ['inventory_staff'],
                'accounting_only' => ['accounting_staff'],
            ];

            if (isset($roleMap[$target->target_type])) {
                $roleUsers = User::query()->whereHas('role', fn (Builder $q) => $q->whereIn('code', $roleMap[$target->target_type]))->pluck('id');
                $userIds = $userIds->merge($roleUsers);
            }
        }

        return $userIds->unique()->values();
    }
}
