<?php

namespace App\Services;

use App\Models\DeploymentLog;

class DeploymentChecklistService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly NotificationService $notificationService,
    ) {
    }

    public function baselineItems(): array
    {
        return [
            'domain_configured' => 'Domain configuration is working',
            'ssl_enabled' => 'SSL certificate is active',
            'app_env_production' => 'APP_ENV is production',
            'app_debug_disabled' => 'APP_DEBUG is disabled',
            'database_connected' => 'Production database is connected',
            'storage_permissions' => 'Storage permissions are correct',
            'queue_scheduler_ready' => 'Queue and scheduler readiness validated',
            'backup_directory_ready' => 'Backup directory is configured and protected',
            'log_directory_ready' => 'Log directory is writable and protected',
        ];
    }

    public function ensureBaseline(): void
    {
        foreach ($this->baselineItems() as $itemKey => $itemLabel) {
            DeploymentLog::query()->firstOrCreate(
                ['item_key' => $itemKey],
                [
                    'deployment_number' => 'DPL-'.now()->format('YmdHis').'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT),
                    'item_label' => $itemLabel,
                    'status' => 'pending',
                ]
            );
        }
    }

    public function paginate()
    {
        $this->ensureBaseline();

        return DeploymentLog::query()
            ->with('checker')
            ->orderBy('item_key')
            ->paginate(30);
    }

    public function updateStatus(DeploymentLog $log, string $status, ?string $remarks = null): DeploymentLog
    {
        $before = $log->toArray();

        $log->update([
            'status' => $status,
            'remarks' => $remarks,
            'checked_by' => auth()->id(),
            'checked_at' => now(),
        ]);

        $this->auditLogService->record('deployment', 'deployment_check_updated', $before, $log->fresh()->toArray(), auth()->user()?->primary_branch_id, 'Deployment checklist item updated');

        if ($status === 'failed') {
            $this->notificationService->create(auth()->id(), null, 'Deployment checklist issue', $log->item_label.' is marked as failed.', 'deployment', ['deployment_log_id' => $log->id]);
        }

        return $log->fresh();
    }
}
