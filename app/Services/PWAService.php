<?php

namespace App\Services;

use App\Models\PwaInstallLog;

class PWAService
{
    public function __construct(
        private readonly DeviceDetectionService $deviceDetectionService,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    public function logInstall(?int $userId, ?int $branchId, ?string $userAgent = null): PwaInstallLog
    {
        $detected = $this->deviceDetectionService->detect($userAgent);

        $log = PwaInstallLog::query()->create([
            'user_id' => $userId,
            'branch_id' => $branchId,
            'platform' => $detected['platform'],
            'browser' => $detected['browser'],
            'device_type' => $detected['device_type'],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'installed_at' => now(),
        ]);

        $this->auditLogService->record('pwa', 'pwa_install_detected', [], $log->toArray(), $branchId, 'PWA installation detected', $userId);

        return $log;
    }
}
