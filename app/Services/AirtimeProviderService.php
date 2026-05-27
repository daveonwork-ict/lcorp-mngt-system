<?php

namespace App\Services;

use App\Models\AirtimeProvider;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AirtimeProviderService
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function paginate(): LengthAwarePaginator
    {
        return AirtimeProvider::query()->latest('provider_name')->paginate(20);
    }

    public function create(array $data): AirtimeProvider
    {
        $provider = AirtimeProvider::query()->create($data);
        $this->auditLogService->record('airtime', 'provider_created', [], $provider->toArray(), null, 'Airtime provider created');

        return $provider;
    }

    public function update(AirtimeProvider $provider, array $data): AirtimeProvider
    {
        $before = $provider->toArray();
        $provider->update($data);
        $this->auditLogService->record('airtime', 'provider_updated', $before, $provider->toArray(), null, 'Airtime provider updated');

        return $provider;
    }
}
