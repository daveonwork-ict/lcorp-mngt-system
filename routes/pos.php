<?php

use App\Http\Controllers\PrototypeController;
use Illuminate\Support\Facades\Route;

Route::get('/pos', fn () => app(PrototypeController::class)->module('pos'))
    ->middleware('permission:pos.view')
    ->name('pos.index');
