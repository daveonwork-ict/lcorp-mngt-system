<?php

use App\Http\Controllers\PrototypeController;
use Illuminate\Support\Facades\Route;

Route::get('/reports', fn () => app(PrototypeController::class)->module('reports'))
    ->middleware('permission:reports.view')
    ->name('reports.index');

Route::get('/audit-trail', fn () => app(PrototypeController::class)->module('audit-trail'))
    ->middleware('permission:audit-trail.view')
    ->name('audit-trail.index');
