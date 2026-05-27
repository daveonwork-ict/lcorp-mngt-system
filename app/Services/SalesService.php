<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Database\DatabaseManager;

class SalesService
{
    public function __construct(
        private readonly DatabaseManager $db,
        private readonly BranchAccessService $branchAccessService,
        private readonly SalesInventoryService $salesInventoryService,
        private readonly PaymentService $paymentService,
        private readonly DiscountService $discountService,
        private readonly SalesAuditService $salesAuditService,
        private readonly NotificationService $notificationService,
        private readonly WarrantyRegistrationService $warrantyRegistrationService,
    ) {
    }

    public function list(array $filters = [])
    {
        $user = auth()->user();

        return Sale::query()
            ->with(['branch', 'cashier', 'payments.paymentMethod'])
            ->when(! $user || $user->role?->code !== config('rms.owner_role_code'), function ($query) use ($user): void {
                $accessible = $user ? $this->branchAccessService->accessibleBranches($user)->pluck('id')->all() : [];
                $query->whereIn('branch_id', $accessible ?: [-1]);
            })
            ->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->when($filters['cashier_id'] ?? null, fn ($q, $cashierId) => $q->where('cashier_id', $cashierId))
            ->when($filters['payment_status'] ?? null, fn ($q, $status) => $q->where('payment_status', $status))
            ->when($filters['sales_status'] ?? null, fn ($q, $status) => $q->where('sales_status', $status))
            ->when($filters['date_from'] ?? null, fn ($q, $date) => $q->whereDate('sales_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($q, $date) => $q->whereDate('sales_date', '<=', $date))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
    }

    public function create(array $payload): Sale
    {
        return $this->db->transaction(function () use ($payload): Sale {
            $branchId = (int) $payload['branch_id'];
            $this->ensureBranchAllowed($branchId);

            $subtotal = 0;
            $itemsToCreate = [];

            foreach ($payload['items'] as $item) {
                $product = Product::query()->findOrFail($item['product_id']);
                $quantity = (int) $item['quantity'];
                $itemDiscount = (float) ($item['discount_amount'] ?? 0);
                $sellingPrice = (float) ($item['selling_price'] ?? $product->selling_price);
                $lineSubtotal = round(($sellingPrice * $quantity) - $itemDiscount, 2);

                if ($lineSubtotal < 0) {
                    abort(422, 'Invalid item subtotal.');
                }

                $this->salesInventoryService->assertSellable($branchId, $product, $quantity, $item['imei_id'] ?? null);

                $subtotal += $lineSubtotal;
                $itemsToCreate[] = [
                    'product' => $product,
                    'payload' => $item,
                    'quantity' => $quantity,
                    'selling_price' => $sellingPrice,
                    'discount_amount' => $itemDiscount,
                    'line_subtotal' => $lineSubtotal,
                ];
            }

            $discountAmount = $this->discountService->compute(
                $subtotal,
                $payload['discount'] ?? null,
                auth()->user()?->hasPermission('apply_discount') ?? false
            );

            $taxAmount = (float) ($payload['tax_amount'] ?? 0);
            $totalAmount = round($subtotal - $discountAmount + $taxAmount, 2);

            $payment = $this->paymentService->validate($totalAmount, $payload['payments'], (bool) ($payload['allow_partial'] ?? false));

            $sale = Sale::query()->create([
                'sales_number' => $payload['sales_number'] ?? ('S-'.now()->format('YmdHis').'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT)),
                'branch_id' => $branchId,
                'cashier_id' => auth()->id(),
                'customer_id' => $payload['customer_id'] ?? null,
                'sales_date' => now()->toDateString(),
                'sales_time' => now()->format('H:i:s'),
                'subtotal_amount' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'paid_amount' => $payment['paid_amount'],
                'change_amount' => $payment['change_amount'],
                'payment_status' => $payment['payment_status'],
                'sales_status' => 'completed',
                'remarks' => $payload['remarks'] ?? null,
            ]);

            foreach ($itemsToCreate as $itemData) {
                $product = $itemData['product'];
                $raw = $itemData['payload'];
                $sale->items()->create([
                    'product_id' => $product->id,
                    'imei_id' => $raw['imei_id'] ?? null,
                    'quantity' => $itemData['quantity'],
                    'cost_price' => (float) $product->cost_price,
                    'selling_price' => $itemData['selling_price'],
                    'discount_amount' => $itemData['discount_amount'],
                    'subtotal' => $itemData['line_subtotal'],
                    'item_status' => 'completed',
                    'warranty_required' => (bool) ($product->warranty_duration > 0),
                    'warranty_status' => $product->warranty_duration > 0 ? 'pending_registration' : null,
                ]);
            }

            $this->paymentService->record($sale, $payment['payments']);
            $this->salesInventoryService->deductForSale($sale);
            $this->warrantyRegistrationService->registerForSale($sale);

            $this->salesAuditService->log('sale_created', [], $sale->toArray(), $sale->branch_id, 'POS sale created');

            if ($sale->total_amount >= 50000) {
                $this->notificationService->create(
                    null,
                    $sale->branch_id,
                    'High-value sale',
                    'High-value sale recorded: '.$sale->sales_number,
                    'sales',
                    ['sale_id' => $sale->id, 'amount' => $sale->total_amount]
                );
            }

            return $sale->fresh(['items.product', 'items.imei', 'payments.paymentMethod', 'branch', 'cashier']);
        });
    }

    public function hold(array $payload)
    {
        $branchId = (int) $payload['branch_id'];
        $this->ensureBranchAllowed($branchId);

        $subtotal = 0;
        $discount = 0;

        $held = \App\Models\HeldTransaction::query()->create([
            'hold_number' => $payload['hold_number'] ?? ('H-'.now()->format('YmdHis').'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT)),
            'branch_id' => $branchId,
            'cashier_id' => auth()->id(),
            'customer_id' => $payload['customer_id'] ?? null,
            'status' => 'held',
            'remarks' => $payload['remarks'] ?? null,
        ]);

        foreach ($payload['items'] as $item) {
            $product = Product::query()->findOrFail($item['product_id']);
            $quantity = (int) $item['quantity'];
            $lineDiscount = (float) ($item['discount_amount'] ?? 0);
            $selling = (float) ($item['selling_price'] ?? $product->selling_price);
            $lineSubtotal = round(($selling * $quantity) - $lineDiscount, 2);

            $subtotal += $lineSubtotal;
            $discount += $lineDiscount;

            $held->items()->create([
                'product_id' => $product->id,
                'imei_id' => $item['imei_id'] ?? null,
                'quantity' => $quantity,
                'selling_price' => $selling,
                'discount_amount' => $lineDiscount,
                'subtotal' => $lineSubtotal,
            ]);
        }

        $held->update([
            'subtotal_amount' => $subtotal,
            'discount_amount' => $discount,
            'total_amount' => $subtotal,
        ]);

        $this->salesAuditService->log('held_transaction_created', [], $held->toArray(), $held->branch_id, 'POS transaction held');

        return $held->fresh('items.product');
    }

    public function ensureCanAccessSale(Sale $sale): void
    {
        $this->ensureBranchAllowed((int) $sale->branch_id);
    }

    private function ensureBranchAllowed(int $branchId): void
    {
        $user = auth()->user();
        if (! $user) {
            abort(401);
        }

        if (! $this->branchAccessService->canAccessBranch($user, $branchId)) {
            abort(403, 'Branch access denied.');
        }
    }
}
