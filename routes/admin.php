<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PrototypeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserBranchController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/approvals', fn () => app(PrototypeController::class)->module('approvals'))
    ->middleware('permission:approvals.view')
    ->name('approvals.index');

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

Route::middleware('permission:view_security_dashboard')->group(function (): void {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('admin.notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('admin.notifications.read-all');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('admin.notifications.read');
});

Route::get('/users-roles', [RoleController::class, 'index'])
    ->middleware('permission:view_roles')
    ->name('users-roles.index');

Route::get('/branches-overview', [BranchController::class, 'index'])
    ->middleware('permission:view_branches')
    ->name('branches.index');

Route::get('/settings', fn () => app(PrototypeController::class)->module('settings'))
    ->middleware('permission:settings.view')
    ->name('settings.index');
