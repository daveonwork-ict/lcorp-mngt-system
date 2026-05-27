<?php

use App\Http\Controllers\BranchDashboardController;
use App\Http\Controllers\ExecutiveDashboardController;
use App\Http\Controllers\PrototypeController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard/owner', [ExecutiveDashboardController::class, 'index'])
    ->middleware('permission:view_executive_dashboard')
    ->name('dashboard.owner');

Route::get('/dashboard/branch', [BranchDashboardController::class, 'index'])
    ->middleware('permission:view_branch_dashboard')
    ->name('dashboard.branch');

Route::post('/branch/switch', [PrototypeController::class, 'switchBranch'])
    ->name('branch.switch');
