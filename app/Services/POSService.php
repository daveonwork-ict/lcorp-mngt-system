<?php

namespace App\Services;

use App\Models\PaymentMethod;
use App\Models\Product;

class POSService
{
    public function __construct(
        private readonly BranchAccessService $branchAccessService,
        private readonly SalesService $salesService,
        private readonly NotificationService $notificationService,
    ) {
    }

    public function posData(?int $branchId = null, array $filters = []): array
    {
        $user = auth()->user();
        if (! $user) {
            abort(401);
        }

        $sessionBranchId = session('active_branch_id');
        $fallbackBranchId = $sessionBranchId
            ?: $user->primary_branch_id
            ?: $this->branchAccessService->accessibleBranches($user)->pluck('id')->first();

        $activeBranchId = $branchId ?? $sessionBranchId ?? $user->primary_branch_id;

        if (! $this->branchAccessService->canAccessBranch($user, $activeBranchId)) {
            if ($fallbackBranchId && $this->branchAccessService->canAccessBranch($user, (int) $fallbackBranchId)) {
                $activeBranchId = (int) $fallbackBranchId;
                session(['active_branch_id' => $activeBranchId]);
            } else {
                abort(403, 'Branch access denied.');
            }
        }

        return [
            'products' => Product::query()
                ->with(['category', 'brand'])
                ->where('status', 'active')
                ->when($filters['search'] ?? null, function ($q, $search): void {
                    $q->where(function ($sub) use ($search): void {
                        $sub->where('product_name', 'like', "%{$search}%")
                            ->orWhere('barcode', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%");
                    });
                })
                ->when($filters['category_id'] ?? null, fn ($q, $categoryId) => $q->where('category_id', $categoryId))
                ->orderBy('product_name')
                ->limit(120)
                ->get(),
            'categories' => \App\Models\ProductCategory::query()->where('status', 'active')->orderBy('category_name')->get(),
            'payment_methods' => PaymentMethod::query()->where('status', 'active')->orderBy('payment_method_name')->get(),
            'held_transactions' => \App\Models\HeldTransaction::query()
                ->where('cashier_id', $user->id)
                ->where('branch_id', $activeBranchId)
                ->where('status', 'held')
                ->latest('id')
                ->limit(20)
                ->get(),
            'active_branch_id' => $activeBranchId,
        ];
    }

    public function checkout(array $payload)
    {
        try {
            return $this->salesService->create($payload);
        } catch (\Throwable $e) {
            $this->notificationService->create(
                auth()->id(),
                $payload['branch_id'] ?? null,
                'Failed checkout',
                'Checkout failed: '.$e->getMessage(),
                'sales',
                ['error' => $e->getMessage()]
            );

            throw $e;
        }
    }
}
