<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class AuditLogService
{
    public function record(
        string $module,
        string $action,
        array $before = [],
        array $after = [],
        ?int $branchId = null,
        ?string $description = null,
        ?int $userId = null
    ): void
    {
        $request = request() instanceof Request ? request() : null;

        ActivityLog::query()->create([
            'user_id' => $userId ?? auth()->id(),
            'branch_id' => $branchId,
            'module' => $module,
            'action' => $action,
            'module_name' => $module,
            'action_type' => $action,
            'description' => $description,
            'before_value' => $before ?: null,
            'after_value' => $after ?: null,
            'before_payload' => $before ?: null,
            'after_payload' => $after ?: null,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }
}
