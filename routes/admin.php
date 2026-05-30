<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\ApprovalHistoryController;
use App\Http\Controllers\ApprovalInboxController;
use App\Http\Controllers\ApprovalRequestController;
use App\Http\Controllers\ApprovalRuleController;
use App\Http\Controllers\AuditTrailController;
use App\Http\Controllers\BackupLogController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\DataImportController;
use App\Http\Controllers\FileAccessController;
use App\Http\Controllers\DeploymentChecklistController;
use App\Http\Controllers\GoLiveChecklistController;
use App\Http\Controllers\LoginSecurityController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\SystemSettingController;
use App\Http\Controllers\SecurityAlertController;
use App\Http\Controllers\SecurityDashboardController;
use App\Http\Controllers\SessionSecurityController;
use App\Http\Controllers\SystemAcceptanceController;
use App\Http\Controllers\TrainingLogController;
use App\Http\Controllers\UserBranchController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('permission:view_approval_inbox')->group(function (): void {
    Route::get('/approvals', [ApprovalInboxController::class, 'index'])->name('approvals.index');
    Route::get('/approvals/inbox', [ApprovalInboxController::class, 'index'])->name('approvals.inbox');
    Route::get('/approvals/requests', [ApprovalRequestController::class, 'index'])->name('approvals.requests.index');
    Route::get('/approvals/requests/{approvalRequest}', [ApprovalRequestController::class, 'show'])->name('approvals.requests.show');
    Route::get('/approvals/history/{approvalRequest}', [ApprovalHistoryController::class, 'show'])->name('approvals.history.show');
});

Route::middleware('permission:manage_approval_rules')->group(function (): void {
    Route::get('/approvals/rules', [ApprovalRuleController::class, 'index'])->name('approvals.rules.index');
    Route::post('/approvals/rules', [ApprovalRuleController::class, 'store'])->name('approvals.rules.store');
    Route::put('/approvals/rules/{approvalRule}', [ApprovalRuleController::class, 'update'])->name('approvals.rules.update');
});

Route::post('/approvals/requests', [ApprovalRequestController::class, 'store'])
    ->middleware('permission:view_approval_inbox')
    ->name('approvals.requests.store');

Route::post('/approvals/requests/{approvalRequest}/approve', [ApprovalRequestController::class, 'approve'])
    ->middleware('permission:approve_requests')
    ->name('approvals.requests.approve');

Route::post('/approvals/requests/{approvalRequest}/reject', [ApprovalRequestController::class, 'reject'])
    ->middleware('permission:reject_requests')
    ->name('approvals.requests.reject');

Route::post('/approvals/requests/{approvalRequest}/return', [ApprovalRequestController::class, 'returnForCorrection'])
    ->middleware('permission:return_requests')
    ->name('approvals.requests.return');

Route::post('/approvals/requests/{approvalRequest}/resubmit', [ApprovalRequestController::class, 'resubmit'])
    ->middleware('permission:view_approval_inbox')
    ->name('approvals.requests.resubmit');

Route::middleware('permission:view_users')->group(function (): void {
    Route::resource('/users', UserController::class)->names('admin.users');
    Route::post('/users/{user}/status', [UserController::class, 'updateStatus'])->name('admin.users.status');
    Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('admin.users.reset-password');
    Route::get('/users/{user}/branches', [UserBranchController::class, 'edit'])->name('admin.users.branches.edit');
    Route::put('/users/{user}/branches', [UserBranchController::class, 'update'])->name('admin.users.branches.update');
});

Route::middleware('permission:view_roles')->group(function (): void {
    Route::resource('/roles', RoleController::class)->names('admin.roles');
    Route::post('/roles/{role}/status', [RoleController::class, 'updateStatus'])->name('admin.roles.status');
});

Route::middleware('permission:view_branches')->group(function (): void {
    Route::resource('/branches', BranchController::class)->names('admin.branches');
    Route::post('/branches/{branch}/status', [BranchController::class, 'updateStatus'])->name('admin.branches.status');
});

Route::middleware('permission:assign_permissions')->group(function (): void {
    Route::get('/permissions', [PermissionController::class, 'index'])->name('admin.permissions.index');
});

Route::middleware('permission:view_audit_logs')->group(function (): void {
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('admin.activity-logs.index');
    Route::get('/login-history', [ActivityLogController::class, 'loginHistory'])->name('admin.security.login-history');
});

Route::middleware('permission:view_audit_trail')->group(function (): void {
    Route::get('/security/audit-trail', [AuditTrailController::class, 'index'])->name('admin.security.audit-trail.index');
});

Route::get('/security/audit-trail/export', [AuditTrailController::class, 'exportCsv'])
    ->middleware('permission:export_audit_trail')
    ->name('admin.security.audit-trail.export');

Route::middleware('permission:view_security_dashboard')->group(function (): void {
    Route::get('/security/dashboard', [SecurityDashboardController::class, 'index'])->name('admin.security.dashboard');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('admin.notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('admin.notifications.read-all');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('admin.notifications.read');
});

Route::middleware('permission:view_login_activity')->group(function (): void {
    Route::get('/security/login-activity', [LoginSecurityController::class, 'index'])->name('admin.security.login-activity');
});

Route::middleware('permission:manage_active_sessions')->group(function (): void {
    Route::get('/security/sessions', [SessionSecurityController::class, 'index'])->name('admin.security.sessions');
    Route::post('/security/sessions/terminate-others', [SessionSecurityController::class, 'terminateOthers'])->name('admin.security.sessions.terminate-others');
    Route::post('/security/sessions/{userSession}/terminate', [SessionSecurityController::class, 'terminate'])->name('admin.security.sessions.terminate');
});

Route::middleware('permission:view_file_access_logs')->group(function (): void {
    Route::get('/security/file-access', [FileAccessController::class, 'index'])->name('admin.security.file-access.index');
});

Route::middleware('permission:view_backup_logs')->group(function (): void {
    Route::get('/security/backup-logs', [BackupLogController::class, 'index'])->name('admin.security.backup-logs.index');
});

Route::post('/security/backup-logs/run', [BackupLogController::class, 'run'])
    ->middleware('permission:manage_backups')
    ->name('admin.security.backup-logs.run');

Route::middleware('permission:view_security_alerts')->group(function (): void {
    Route::get('/security/alerts', [SecurityAlertController::class, 'index'])->name('admin.security.alerts.index');
    Route::post('/security/alerts/{securityAlert}/resolve', [SecurityAlertController::class, 'resolve'])->name('admin.security.alerts.resolve');
});

Route::get('/users-roles', [RoleController::class, 'index'])
    ->middleware('permission:view_roles')
    ->name('users-roles.index');

Route::get('/branches-overview', [BranchController::class, 'index'])
    ->middleware('permission:view_branches')
    ->name('branches.index');

Route::middleware('permission:settings.view')->group(function (): void {
    Route::get('/settings', [SystemSettingController::class, 'index'])->name('settings.index');
    Route::put('/settings/branding', [SystemSettingController::class, 'updateBranding'])->name('settings.branding.update');
});

Route::middleware('permission:view_deployment_checklists')->group(function (): void {
    Route::get('/deployment/checklists', [DeploymentChecklistController::class, 'index'])->name('deployment.checklists.index');
    Route::put('/deployment/checklists/{deploymentLog}', [DeploymentChecklistController::class, 'update'])->name('deployment.checklists.update');
    Route::get('/deployment/imports', [DataImportController::class, 'index'])->name('deployment.imports.index');
    Route::post('/deployment/imports', [DataImportController::class, 'store'])->name('deployment.imports.store');
    Route::get('/deployment/imports/{dataImportLog}', [DataImportController::class, 'show'])->name('deployment.imports.show');
    Route::post('/deployment/imports/{dataImportLog}/confirm', [DataImportController::class, 'confirm'])->name('deployment.imports.confirm');
    Route::get('/deployment/imports/{module}/template', [DataImportController::class, 'template'])->name('deployment.imports.template');
    Route::get('/deployment/training', [TrainingLogController::class, 'index'])->name('deployment.training.index');
    Route::post('/deployment/training', [TrainingLogController::class, 'store'])->name('deployment.training.store');
    Route::get('/deployment/go-live', [GoLiveChecklistController::class, 'index'])->name('deployment.go-live.index');
    Route::put('/deployment/go-live/{goLiveChecklist}', [GoLiveChecklistController::class, 'update'])->name('deployment.go-live.update');
    Route::get('/deployment/support', [SupportTicketController::class, 'index'])->name('deployment.support.index');
    Route::post('/deployment/support', [SupportTicketController::class, 'store'])->name('deployment.support.store');
    Route::put('/deployment/support/{supportTicket}', [SupportTicketController::class, 'update'])->name('deployment.support.update');
    Route::get('/deployment/acceptance', [SystemAcceptanceController::class, 'index'])->name('deployment.acceptance.index');
    Route::post('/deployment/acceptance', [SystemAcceptanceController::class, 'store'])->name('deployment.acceptance.store');
});
