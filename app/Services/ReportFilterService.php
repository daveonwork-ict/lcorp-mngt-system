<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class ReportFilterService
{
    public function __construct(private readonly BranchAccessService $branchAccessService)
    {
    }

    public function normalize(array $input): array
    {
        return [
            'date_from' => $input['date_from'] ?? null,
            'date_to' => $input['date_to'] ?? null,
            'branch_id' => isset($input['branch_id']) && $input['branch_id'] !== '' ? (int) $input['branch_id'] : null,
            'user_id' => isset($input['user_id']) && $input['user_id'] !== '' ? (int) $input['user_id'] : null,
            'product_id' => isset($input['product_id']) && $input['product_id'] !== '' ? (int) $input['product_id'] : null,
            'category_id' => isset($input['category_id']) && $input['category_id'] !== '' ? (int) $input['category_id'] : null,
            'brand_id' => isset($input['brand_id']) && $input['brand_id'] !== '' ? (int) $input['brand_id'] : null,
            'provider_id' => isset($input['provider_id']) && $input['provider_id'] !== '' ? (int) $input['provider_id'] : null,
            'status' => $input['status'] ?? null,
            'payment_method_id' => isset($input['payment_method_id']) && $input['payment_method_id'] !== '' ? (int) $input['payment_method_id'] : null,
            'cashier_id' => isset($input['cashier_id']) && $input['cashier_id'] !== '' ? (int) $input['cashier_id'] : null,
            'customer_id' => isset($input['customer_id']) && $input['customer_id'] !== '' ? (int) $input['customer_id'] : null,
            'warranty_status' => $input['warranty_status'] ?? null,
            'expense_category_id' => isset($input['expense_category_id']) && $input['expense_category_id'] !== '' ? (int) $input['expense_category_id'] : null,
        ];
    }

    public function enforceBranchScope(User $user, ?int $branchId = null): ?int
    {
        if ($this->branchAccessService->hasGlobalBranchAccess($user)) {
            return $branchId;
        }

        if ($branchId && ! $this->branchAccessService->canAccessBranch($user, $branchId)) {
            abort(403, 'Branch access denied.');
        }

        if ($branchId) {
            return $branchId;
        }

        return $user->primary_branch_id;
    }

    public function scopeQueryByBranch(Builder $query, User $user, string $column = 'branch_id', ?int $branchId = null): Builder
    {
        $resolvedBranchId = $this->enforceBranchScope($user, $branchId);

        if ($resolvedBranchId) {
            return $query->where($column, $resolvedBranchId);
        }

        if (! $this->branchAccessService->hasGlobalBranchAccess($user)) {
            $allowed = $this->branchAccessService->accessibleBranches($user)->pluck('id')->all();
            return $query->whereIn($column, $allowed ?: [-1]);
        }

        return $query;
    }
}
