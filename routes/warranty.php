<?php

use App\Http\Controllers\PrototypeController;
use Illuminate\Support\Facades\Route;

Route::get('/warranty', fn () => app(PrototypeController::class)->module('warranty'))
    ->middleware('permission:warranty.view')
    ->name('warranty.index');

Route::get('/customers', fn () => app(PrototypeController::class)->module('customers'))
    ->middleware('permission:customers.view')
    ->name('customers.index');
