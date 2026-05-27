<?php

use App\Http\Controllers\PrototypeController;
use Illuminate\Support\Facades\Route;

Route::get('/airtime', fn () => app(PrototypeController::class)->module('airtime'))
    ->middleware('permission:airtime.view')
    ->name('airtime.index');
