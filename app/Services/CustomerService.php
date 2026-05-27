<?php

namespace App\Services;

use App\Models\Customer;

class CustomerService
{
    public function __construct(
        private readonly BranchAccessService $branchAccessService,
        private readonly CustomerDuplicateCheckService $duplicateCheckService,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    public function paginate(array $filters = [])
    {
        return Customer::query()
            ->when($filters['search'] ?? null, function ($q, $search): void {
                $q->where(function ($w) use ($search): void {
                    $w->where('full_name', 'like', "%{$search}%")
                        ->orWhere('mobile_number', 'like', "%{$search}%")
                        ->orWhere('customer_code', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
    }

    public function create(array $payload): Customer
    {
        $fullName = trim(implode(' ', array_filter([
            $payload['first_name'] ?? null,
            $payload['middle_name'] ?? null,
            $payload['last_name'] ?? null,
            $payload['suffix'] ?? null,
        ])));

        if ($this->duplicateCheckService->exists($payload['mobile_number'], $fullName)) {
            abort(422, 'Duplicate customer detected using mobile number or full name.');
        }

        $customer = Customer::query()->create([
            'customer_code' => $payload['customer_code'] ?? ('CUS-'.now()->format('YmdHis').'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT)),
            'first_name' => $payload['first_name'],
            'middle_name' => $payload['middle_name'] ?? null,
            'last_name' => $payload['last_name'],
            'suffix' => $payload['suffix'] ?? null,
            'full_name' => $fullName,
            'mobile_number' => $payload['mobile_number'],
            'email' => $payload['email'] ?? null,
            'address' => $payload['address'] ?? null,
            'birthdate' => $payload['birthdate'] ?? null,
            'gender' => $payload['gender'] ?? null,
            'customer_type' => $payload['customer_type'] ?? 'walk_in',
            'status' => $payload['status'] ?? 'active',
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        $this->auditLogService->record('warranty', 'customer_created', [], $customer->toArray(), null, 'Customer created');

        return $customer;
    }

    public function update(Customer $customer, array $payload): Customer
    {
        $fullName = trim(implode(' ', array_filter([
            $payload['first_name'] ?? $customer->first_name,
            $payload['middle_name'] ?? $customer->middle_name,
            $payload['last_name'] ?? $customer->last_name,
            $payload['suffix'] ?? $customer->suffix,
        ])));

        if ($this->duplicateCheckService->exists($payload['mobile_number'] ?? $customer->mobile_number, $fullName, $customer->id)) {
            abort(422, 'Duplicate customer detected using mobile number or full name.');
        }

        $before = $customer->toArray();

        $customer->update([
            'first_name' => $payload['first_name'] ?? $customer->first_name,
            'middle_name' => $payload['middle_name'] ?? null,
            'last_name' => $payload['last_name'] ?? $customer->last_name,
            'suffix' => $payload['suffix'] ?? null,
            'full_name' => $fullName,
            'mobile_number' => $payload['mobile_number'] ?? $customer->mobile_number,
            'email' => $payload['email'] ?? null,
            'address' => $payload['address'] ?? null,
            'birthdate' => $payload['birthdate'] ?? null,
            'gender' => $payload['gender'] ?? null,
            'customer_type' => $payload['customer_type'] ?? $customer->customer_type,
            'status' => $payload['status'] ?? $customer->status,
            'updated_by' => auth()->id(),
        ]);

        $this->auditLogService->record('warranty', 'customer_updated', $before, $customer->toArray(), null, 'Customer updated');

        return $customer;
    }

    public function deactivate(Customer $customer): Customer
    {
        $before = $customer->toArray();
        $customer->update(['status' => 'inactive', 'updated_by' => auth()->id()]);
        $this->auditLogService->record('warranty', 'customer_deactivated', $before, $customer->toArray(), null, 'Customer deactivated');

        return $customer;
    }
}
