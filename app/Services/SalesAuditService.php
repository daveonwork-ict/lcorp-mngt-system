<?php

namespace App\Services;

class SalesAuditService
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function log(string $action, array $before = [], array $after = [], ?int $branchId = null, ?string $description = null): void
    {
        $this->auditLogService->record('sales', $action, $before, $after, $branchId, $description);
    }
}
