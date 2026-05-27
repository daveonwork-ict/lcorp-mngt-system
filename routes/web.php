<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard/owner');

require __DIR__.'/auth.php';

Route::middleware(['auth', 'branch.access', 'session.track'])->group(function (): void {
    require __DIR__.'/dashboard.php';
    require __DIR__.'/inventory.php';
    require __DIR__.'/pos.php';
    require __DIR__.'/airtime.php';
    require __DIR__.'/finance.php';
    require __DIR__.'/warranty.php';
    require __DIR__.'/communication.php';
    require __DIR__.'/reports.php';
    require __DIR__.'/admin.php';
});
