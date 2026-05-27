<?php

namespace App\Services;

use App\Models\FileAccessLog;
use App\Models\LoginAttemptLog;

class SecurityLogService
{
    public function __construct(private readonly SecurityAlertService $alertService)
    {
    }

    public function logLoginAttempt(?int $userId, ?string $identifier, string $status, ?string $remarks = null): LoginAttemptLog
    {
        $log = LoginAttemptLog::query()->create([
            'user_id' => $userId,
            'login_identifier' => $identifier,
            'status' => $status,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'device_information' => $this->deviceInfo(),
            'logged_at' => now(),
            'remarks' => $remarks,
        ]);

        if ($status === 'failed') {
            $recentFailed = LoginAttemptLog::query()
                ->where('ip_address', request()->ip())
                ->where('status', 'failed')
                ->where('logged_at', '>=', now()->subMinutes(10))
                ->count();

            if ($recentFailed >= 5) {
                $this->alertService->raise('multiple_failed_login', 'high', 'Multiple failed login attempts detected from '.$log->ip_address, $userId, null, 'auth', ['count' => $recentFailed]);
            }
        }

        return $log;
    }

    public function logFileAccess(array $payload): FileAccessLog
    {
        $log = FileAccessLog::query()->create([
            'user_id' => $payload['user_id'] ?? auth()->id(),
            'branch_id' => $payload['branch_id'] ?? null,
            'module_name' => $payload['module_name'],
            'reference_type' => $payload['reference_type'] ?? null,
            'reference_id' => $payload['reference_id'] ?? null,
            'file_name' => $payload['file_name'] ?? null,
            'file_path' => $payload['file_path'] ?? null,
            'action_type' => $payload['action_type'] ?? 'download',
            'status' => $payload['status'] ?? 'success',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'accessed_at' => now(),
        ]);

        if (($payload['status'] ?? 'success') !== 'success') {
            $this->alertService->raise('suspicious_file_access', 'medium', 'Suspicious file access was detected.', $log->user_id, $log->branch_id, $log->module_name, ['file' => $log->file_name]);
        }

        return $log;
    }

    private function deviceInfo(): string
    {
        $agent = (string) request()->userAgent();

        return strlen($agent) > 190 ? substr($agent, 0, 190) : $agent;
    }
}
