<?php

use App\Http\Controllers\PrototypeController;
use Illuminate\Support\Facades\Route;

Route::get('/approvals', fn () => app(PrototypeController::class)->module('approvals'))
    ->middleware('permission:approvals.view')
    ->name('approvals.index');

Route::get('/users-roles', fn () => app(PrototypeController::class)->module('users-roles'))
    ->middleware('permission:users-roles.view')
    ->name('users-roles.index');

Route::get('/branches', fn () => app(PrototypeController::class)->module('branches'))
    ->middleware('permission:branches.view')
    ->name('branches.index');

Route::get('/settings', fn () => app(PrototypeController::class)->module('settings'))
    ->middleware('permission:settings.view')
    ->name('settings.index');
