<?php

namespace App\Services;

use App\Models\Supplier;

class SupplierService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly NotificationService $notificationService,
    ) {
    }

    public function paginate(array $filters = [])
    {
        return Supplier::query()
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where(function ($query) use ($search): void {
                $query->where('supplier_name', 'like', "%{$search}%")
                    ->orWhere('supplier_code', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%");
            }))
            ->orderBy('supplier_name')
            ->paginate(20)
            ->withQueryString();
    }

    public function create(array $payload): Supplier
    {
        $supplier = Supplier::query()->create([
            'supplier_code' => $payload['supplier_code'] ?? ('SUP-'.now()->format('YmdHis').'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT)),
            'supplier_name' => $payload['supplier_name'],
            'contact_person' => $payload['contact_person'] ?? null,
            'contact_number' => $payload['contact_number'] ?? null,
            'email' => $payload['email'] ?? null,
            'address' => $payload['address'] ?? null,
            'product_categories' => $payload['product_categories'] ?? null,
            'payment_terms' => $payload['payment_terms'] ?? null,
            'status' => $payload['status'] ?? 'active',
            'remarks' => $payload['remarks'] ?? null,
        ]);

        $this->auditLogService->record('purchasing', 'supplier_created', [], $supplier->toArray(), null, 'Supplier created');

        return $supplier;
    }

    public function update(Supplier $supplier, array $payload): Supplier
    {
        $before = $supplier->toArray();

        $supplier->update([
            'supplier_name' => $payload['supplier_name'],
            'contact_person' => $payload['contact_person'] ?? null,
            'contact_number' => $payload['contact_number'] ?? null,
            'email' => $payload['email'] ?? null,
            'address' => $payload['address'] ?? null,
            'product_categories' => $payload['product_categories'] ?? null,
            'payment_terms' => $payload['payment_terms'] ?? null,
            'status' => $payload['status'] ?? $supplier->status,
            'remarks' => $payload['remarks'] ?? null,
        ]);

        $this->auditLogService->record('purchasing', 'supplier_updated', $before, $supplier->fresh()->toArray(), null, 'Supplier updated');

        return $supplier->fresh();
    }

    public function profile(Supplier $supplier): array
    {
        return [
            'total_purchase_orders' => $supplier->purchaseOrders()->count(),
            'approved_purchase_orders' => $supplier->purchaseOrders()->where('status', 'approved')->count(),
            'active_payables' => (float) $supplier->payables()->sum('balance_amount'),
            'payments_mtd' => (float) $supplier->payments()->whereDate('payment_date', '>=', now()->startOfMonth())->sum('amount_paid'),
        ];
    }
}
