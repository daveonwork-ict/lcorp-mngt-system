<?php

namespace App\Services;

use App\Models\SecurityAlert;

class SecurityAlertService
{
    public function __construct(private readonly NotificationService $notificationService)
    {
    }

    public function raise(string $alertType, string $severity, string $message, ?int $userId = null, ?int $branchId = null, ?string $moduleName = null, array $context = []): SecurityAlert
    {
        $alert = SecurityAlert::query()->create([
            'user_id' => $userId,
            'branch_id' => $branchId,
            'alert_type' => $alertType,
            'severity' => $severity,
            'module_name' => $moduleName,
            'message' => $message,
            'context_payload' => $context ?: null,
            'is_resolved' => false,
            'alerted_at' => now(),
        ]);

        $this->notificationService->create(null, $branchId, 'Security alert: '.$alertType, $message, 'security', ['security_alert_id' => $alert->id]);

        return $alert;
    }

    public function resolve(SecurityAlert $alert): SecurityAlert
    {
        $alert->update([
            'is_resolved' => true,
            'resolved_by' => auth()->id(),
            'resolved_at' => now(),
        ]);

        return $alert->fresh();
    }

    public function paginate(array $filters = [])
    {
        return SecurityAlert::query()
            ->with(['user', 'branch', 'resolver'])
            ->when($filters['severity'] ?? null, fn ($q, $severity) => $q->where('severity', $severity))
            ->when($filters['alert_type'] ?? null, fn ($q, $type) => $q->where('alert_type', $type))
            ->when(isset($filters['is_resolved']) && $filters['is_resolved'] !== '', fn ($q) => $q->where('is_resolved', (bool) $filters['is_resolved']))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
    }
}
