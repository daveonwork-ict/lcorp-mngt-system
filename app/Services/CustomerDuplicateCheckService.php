<?php

namespace App\Services;

use App\Models\Customer;

class CustomerDuplicateCheckService
{
    public function exists(string $mobileNumber, ?string $fullName = null, ?int $exceptId = null): bool
    {
        return Customer::query()
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
            ->where(function ($query) use ($mobileNumber, $fullName): void {
                $query->where('mobile_number', $mobileNumber);
                if ($fullName) {
                    $query->orWhere('full_name', $fullName);
                }
            })
            ->exists();
    }
}
