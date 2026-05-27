<?php

use App\Http\Controllers\PrototypeController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard/owner', [PrototypeController::class, 'ownerDashboard'])
    ->middleware('permission:dashboard.owner.view')
    ->name('dashboard.owner');

Route::get('/dashboard/branch', [PrototypeController::class, 'branchDashboard'])
    ->middleware('permission:dashboard.branch.view')
    ->name('dashboard.branch');

Route::post('/branch/switch', [PrototypeController::class, 'switchBranch'])
    ->name('branch.switch');
