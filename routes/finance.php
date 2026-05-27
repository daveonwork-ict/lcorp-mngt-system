<?php

use App\Http\Controllers\PrototypeController;
use Illuminate\Support\Facades\Route;

Route::get('/cash-flow', fn () => app(PrototypeController::class)->module('cash-flow'))
    ->middleware('permission:cash-flow.view')
    ->name('cash-flow.index');

Route::get('/expenses', fn () => app(PrototypeController::class)->module('expenses'))
    ->middleware('permission:expenses.view')
    ->name('expenses.index');
