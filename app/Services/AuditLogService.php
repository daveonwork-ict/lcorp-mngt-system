<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

        $payload = [
            'user_id' => $userId ?? auth()->id(),
            'branch_id' => $branchId,
            'module_name' => $module,
            'action_type' => $action,
            'reference_type' => null,
            'reference_id' => null,
            'before_value' => $before ?: null,
            'after_value' => $after ?: null,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'device_information' => $this->deviceInformation($request?->userAgent()),
        ];

        AuditLog::query()->create(array_merge($payload, [
            'audit_number' => $this->generateAuditNumber(),
        ]));

        ActivityLog::query()->create([
            'user_id' => $payload['user_id'],
            'branch_id' => $payload['branch_id'],
            'module' => $module,
            'action' => $action,
            'module_name' => $module,
            'action_type' => $action,
            'description' => $description,
            'before_value' => $payload['before_value'],
            'after_value' => $payload['after_value'],
            'before_payload' => $payload['before_value'],
            'after_payload' => $payload['after_value'],
            'ip_address' => $payload['ip_address'],
            'user_agent' => $payload['user_agent'],
        ]);
    }

    private function deviceInformation(?string $userAgent): ?string
    {
        if (! $userAgent) {
            return null;
        }

        return strlen($userAgent) > 190 ? substr($userAgent, 0, 190) : $userAgent;
    }

    private function generateAuditNumber(): string
    {
        return 'AUD-'.now()->format('YmdHisv').'-'.Str::upper(Str::random(6));
    }
}
