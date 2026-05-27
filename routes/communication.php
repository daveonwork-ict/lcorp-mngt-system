<?php

use App\Http\Controllers\PrototypeController;
use Illuminate\Support\Facades\Route;

Route::get('/announcements', fn () => app(PrototypeController::class)->module('announcements'))
    ->middleware('permission:announcements.view')
    ->name('announcements.index');

Route::get('/chat', fn () => app(PrototypeController::class)->module('chat'))
    ->middleware('permission:chat.view')
    ->name('chat.index');
