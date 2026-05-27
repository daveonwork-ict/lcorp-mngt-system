<?php

namespace App\Http\Controllers;

use App\Models\ApprovalRequest;
use App\Models\AuditLog;
use App\Models\FileAccessLog;
use App\Models\LoginAttemptLog;
use App\Models\SecurityAlert;
use App\Models\UserSession;
use Illuminate\View\View;

class SecurityDashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.security.dashboard', [
            'cards' => [
                'failed_login_attempts' => LoginAttemptLog::query()->where('status', 'failed')->whereDate('logged_at', '>=', now()->subDays(7))->count(),
                'active_users' => UserSession::query()->where('status', 'active')->distinct('user_id')->count('user_id'),
                'recent_audit_logs' => AuditLog::query()->whereDate('created_at', '>=', now()->subDays(1))->count(),
                'pending_approvals' => ApprovalRequest::query()->whereIn('status', ['pending', 'under_review'])->count(),
                'high_priority_approvals' => ApprovalRequest::query()->whereIn('priority', ['urgent', 'critical'])->whereIn('status', ['pending', 'under_review'])->count(),
                'suspicious_activities' => SecurityAlert::query()->where('is_resolved', false)->whereIn('severity', ['high', 'critical'])->count(),
                'recent_file_downloads' => FileAccessLog::query()->whereDate('accessed_at', '>=', now()->subDays(1))->count(),
                'recent_permission_changes' => AuditLog::query()->where('action_type', 'like', '%permission%')->whereDate('created_at', '>=', now()->subDays(7))->count(),
            ],
            'failedLogins' => LoginAttemptLog::query()->where('status', 'failed')->latest('id')->limit(10)->get(),
            'recentAudit' => AuditLog::query()->latest('id')->limit(10)->get(),
            'activeSessions' => UserSession::query()->with('user')->where('status', 'active')->latest('id')->limit(10)->get(),
            'approvalActions' => \App\Models\ApprovalRequestLog::query()->with('performer')->latest('id')->limit(10)->get(),
            'alerts' => SecurityAlert::query()->where('is_resolved', false)->latest('id')->limit(10)->get(),
        ]);
    }
}
