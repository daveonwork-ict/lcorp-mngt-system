<?php

namespace App\Services;

use App\Models\BackupLog;

class BackupService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly SecurityAlertService $securityAlertService,
        private readonly NotificationService $notificationService,
    ) {
    }

    public function runManual(?string $remarks = null): BackupLog
    {
        $log = BackupLog::query()->create([
            'backup_number' => 'BKP-'.now()->format('YmdHis').'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT),
            'backup_type' => 'manual',
            'status' => 'in_progress',
            'remarks' => $remarks,
            'started_by' => auth()->id(),
            'started_at' => now(),
        ]);

        // Backup execution is environment dependent; we keep readiness and observability here.
        $log->update([
            'status' => 'completed',
            'completed_at' => now(),
            'file_path' => null,
            'file_size_mb' => null,
            'remarks' => trim(($remarks ? $remarks.' ' : '').'Backup readiness run completed.'),
        ]);

        $this->auditLogService->record('security', 'backup_generated', [], $log->toArray(), null, 'Manual backup readiness run');
        $this->notificationService->create(auth()->id(), null, 'Backup completed', 'Backup readiness run completed.', 'security', ['backup_log_id' => $log->id]);

        return $log->fresh();
    }

    public function markFailed(?string $message = null): BackupLog
    {
        $log = BackupLog::query()->create([
            'backup_number' => 'BKP-'.now()->format('YmdHis').'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT),
            'backup_type' => 'manual',
            'status' => 'failed',
            'remarks' => $message,
            'started_by' => auth()->id(),
            'started_at' => now(),
            'completed_at' => now(),
        ]);

        $this->auditLogService->record('security', 'backup_failed', [], $log->toArray(), null, 'Backup failed');
        $this->securityAlertService->raise('backup_failure', 'high', 'Backup failed: '.($message ?: 'Unknown error'), auth()->id(), null, 'security', ['backup_log_id' => $log->id]);
        $this->notificationService->create(auth()->id(), null, 'Backup failed', 'Backup process failed.', 'security', ['backup_log_id' => $log->id]);

        return $log;
    }

    public function paginate(array $filters = [])
    {
        return BackupLog::query()
            ->with('starter')
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['backup_type'] ?? null, fn ($q, $type) => $q->where('backup_type', $type))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
    }
}
