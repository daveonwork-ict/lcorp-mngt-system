<?php

use App\Http\Controllers\DevicePreferenceController;
use App\Http\Controllers\PWAController;
use App\Http\Controllers\PushSubscriptionController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard/owner');

Route::get('/offline', [PWAController::class, 'offline'])->name('pwa.offline');
Route::get('/pwa/status', [PWAController::class, 'status'])->name('pwa.status');
Route::post('/pwa/installed', [PWAController::class, 'installed'])->name('pwa.installed');

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

    Route::post('/pwa/push-subscriptions', [PushSubscriptionController::class, 'store'])->name('pwa.push-subscriptions.store');
    Route::delete('/pwa/push-subscriptions', [PushSubscriptionController::class, 'destroy'])->name('pwa.push-subscriptions.destroy');
    Route::post('/pwa/device-preferences', [DevicePreferenceController::class, 'upsert'])->name('pwa.device-preferences.upsert');
});
