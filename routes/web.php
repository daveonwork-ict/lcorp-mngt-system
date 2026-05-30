<?php

use App\Http\Controllers\DevicePreferenceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PWAController;
use App\Http\Controllers\PushSubscriptionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {
    $user = $request->user();

    if (! $user) {
        return redirect()->route('login');
    }

    if ($user->hasPermission('view_executive_dashboard') || $user->role?->code === config('rms.owner_role_code')) {
        return redirect()->route('dashboard.owner');
    }

    if ($user->hasPermission('view_branch_dashboard')) {
        return redirect()->route('dashboard.branch');
    }

    $fallbackRoutes = [
        'view_attendance' => 'hr.attendance.index',
        'view_inventory' => 'inventory.index',
        'view_pos' => 'pos.index',
        'view_airtime_dashboard' => 'airtime.index',
        'view_cash_flow' => 'cash-flow.index',
        'view_reports' => 'reports.index',
        'view_approval_inbox' => 'approvals.index',
    ];

    foreach ($fallbackRoutes as $permission => $routeName) {
        if ($user->hasPermission($permission)) {
            return redirect()->route($routeName);
        }
    }

    return redirect()->route('login');
});

Route::get('/offline', [PWAController::class, 'offline'])->name('pwa.offline');
Route::get('/pwa/status', [PWAController::class, 'status'])->name('pwa.status');
Route::post('/pwa/installed', [PWAController::class, 'installed'])->name('pwa.installed');

require __DIR__.'/auth.php';

Route::middleware(['auth', 'branch.access', 'session.track'])->group(function (): void {
    Route::get('/my-profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/my-profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/my-profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    require __DIR__.'/dashboard.php';
    require __DIR__.'/hr.php';
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
