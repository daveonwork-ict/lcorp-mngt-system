<?php

namespace App\Services;

use App\Models\ApprovalRule;
use App\Models\User;

class ApprovalRoutingService
{
    public function matchedRules(string $moduleName, ?string $transactionType, ?int $branchId, ?float $amount = null, ?int $requesterRoleId = null)
    {
        return ApprovalRule::query()
            ->where('module_name', $moduleName)
            ->when($transactionType, fn ($q, $value) => $q->where(function ($scope) use ($value): void {
                $scope->where('transaction_type', $value)->orWhereNull('transaction_type');
            }))
            ->when($branchId, fn ($q, $value) => $q->where(function ($scope) use ($value): void {
                $scope->where('branch_id', $value)->orWhereNull('branch_id');
            }))
            ->when($requesterRoleId, fn ($q, $value) => $q->where(function ($scope) use ($value): void {
                $scope->where('role_id', $value)->orWhereNull('role_id');
            }))
            ->when($amount !== null, fn ($q) => $q
                ->where(function ($scope) use ($amount): void {
                    $scope->whereNull('minimum_amount')->orWhere('minimum_amount', '<=', $amount);
                })
                ->where(function ($scope) use ($amount): void {
                    $scope->whereNull('maximum_amount')->orWhere('maximum_amount', '>=', $amount);
                }))
            ->where('status', 'active')
            ->orderBy('approval_level')
            ->get();
    }

    public function currentApproverForRule(ApprovalRule $rule, ?int $branchId, int $excludeUserId): ?User
    {
        $query = User::query()->where('role_id', $rule->approver_role_id)->where('is_active', true);

        if ($branchId && ! $rule->requires_owner_approval) {
            $query->where(function ($scope) use ($branchId): void {
                $scope->where('primary_branch_id', $branchId)
                    ->orWhereHas('branches', fn ($q) => $q->where('branches.id', $branchId));
            });
        }

        $query->where('id', '!=', $excludeUserId);

        return $query->orderBy('id')->first();
    }
}
