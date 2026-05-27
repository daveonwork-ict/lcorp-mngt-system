<?php

use App\Http\Controllers\PrototypeController;
use Illuminate\Support\Facades\Route;

Route::get('/inventory', fn () => app(PrototypeController::class)->module('inventory'))
    ->middleware('permission:inventory.view')
    ->name('inventory.index');

Route::get('/suppliers', fn () => app(PrototypeController::class)->module('suppliers'))
    ->middleware('permission:suppliers.view')
    ->name('suppliers.index');

Route::get('/purchasing', fn () => app(PrototypeController::class)->module('purchasing'))
    ->middleware('permission:purchasing.view')
    ->name('purchasing.index');

Route::get('/office-supplies', fn () => app(PrototypeController::class)->module('office-supplies'))
    ->middleware('permission:office-supplies.view')
    ->name('office-supplies.index');
