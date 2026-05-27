<?php

namespace App\Services;

use App\Models\LoginAttemptLog;

class LoginSecurityService
{
    public function __construct(private readonly SecurityLogService $securityLogService)
    {
    }

    public function logSuccess(?int $userId, ?string $identifier = null): LoginAttemptLog
    {
        return $this->securityLogService->logLoginAttempt($userId, $identifier, 'success');
    }

    public function logFailure(?int $userId, ?string $identifier = null, ?string $remarks = null): LoginAttemptLog
    {
        return $this->securityLogService->logLoginAttempt($userId, $identifier, 'failed', $remarks);
    }

    public function logLogout(?int $userId, ?string $identifier = null): LoginAttemptLog
    {
        return $this->securityLogService->logLoginAttempt($userId, $identifier, 'logout');
    }

    public function history(array $filters = [])
    {
        return LoginAttemptLog::query()
            ->with('user')
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['date_from'] ?? null, fn ($q, $date) => $q->whereDate('logged_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($q, $date) => $q->whereDate('logged_at', '<=', $date))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
    }
}
