<?php

namespace App\Services;

use App\Models\UserSession;

class SessionSecurityService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly SecurityAlertService $alertService,
    ) {
    }

    public function registerCurrentSession(): UserSession
    {
        $user = auth()->user();
        if (! $user) {
            abort(403, 'Authentication required.');
        }

        $sessionId = (string) session()->getId();

        $record = UserSession::query()->updateOrCreate(
            ['session_id' => $sessionId],
            [
                'user_id' => $user->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'device_information' => request()->userAgent(),
                'last_activity_at' => now(),
                'terminated_at' => null,
                'terminated_by' => null,
                'status' => 'active',
            ]
        );

        $activeCount = UserSession::query()->where('user_id', $user->id)->where('status', 'active')->count();
        if ($activeCount > 3) {
            $this->alertService->raise('multiple_active_sessions', 'medium', 'User has multiple active sessions.', $user->id, $user->primary_branch_id, 'auth', ['active_sessions' => $activeCount]);
        }

        return $record;
    }

    public function heartbeat(): void
    {
        $sessionId = (string) session()->getId();
        UserSession::query()->where('session_id', $sessionId)->where('status', 'active')->update(['last_activity_at' => now()]);
    }

    public function activeSessions(array $filters = [])
    {
        return UserSession::query()
            ->with(['user', 'terminator'])
            ->when($filters['user_id'] ?? null, fn ($q, $userId) => $q->where('user_id', $userId))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
    }

    public function terminate(UserSession $userSession): UserSession
    {
        if ($userSession->status !== 'active') {
            return $userSession;
        }

        $userSession->update([
            'status' => 'terminated',
            'terminated_at' => now(),
            'terminated_by' => auth()->id(),
        ]);

        $this->auditLogService->record('security', 'session_terminated', [], $userSession->toArray(), $userSession->user?->primary_branch_id, 'User session terminated');

        return $userSession->fresh();
    }

    public function terminateOtherSessionsForCurrentUser(): int
    {
        $user = auth()->user();
        if (! $user) {
            abort(403);
        }

        $current = (string) session()->getId();

        $query = UserSession::query()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->where('session_id', '!=', $current);

        $count = $query->count();
        $query->update([
            'status' => 'terminated',
            'terminated_at' => now(),
            'terminated_by' => $user->id,
        ]);

        $this->auditLogService->record('security', 'other_sessions_terminated', [], ['count' => $count], $user->primary_branch_id, 'User terminated own other sessions');

        return $count;
    }
}
