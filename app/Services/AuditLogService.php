<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class AuditLogService
{
    public function record(string $module, string $action, array $before = [], array $after = [], ?int $branchId = null): void
    {
        ActivityLog::query()->create([
            'user_id' => auth()->id(),
            'branch_id' => $branchId,
            'module' => $module,
            'action' => $action,
            'before_payload' => $before ?: null,
            'after_payload' => $after ?: null,
            'ip_address' => request() instanceof Request ? request()->ip() : null,
        ]);
    }
}
