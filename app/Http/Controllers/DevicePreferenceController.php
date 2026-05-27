<?php

namespace App\Http\Controllers;

use App\Services\PushNotificationPreparationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DevicePreferenceController extends Controller
{
    public function __construct(private readonly PushNotificationPreparationService $service)
    {
    }

    public function upsert(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'preferences' => ['nullable', 'array'],
            'allow_low_stock' => ['nullable', 'boolean'],
            'allow_cash_variance' => ['nullable', 'boolean'],
            'allow_pending_approval' => ['nullable', 'boolean'],
            'allow_announcement' => ['nullable', 'boolean'],
            'allow_chat_message' => ['nullable', 'boolean'],
            'allow_low_wallet' => ['nullable', 'boolean'],
            'allow_warranty_update' => ['nullable', 'boolean'],
            'allow_daily_closing' => ['nullable', 'boolean'],
        ]);

        $user = $request->user();
        if (! $user) {
            abort(403, 'Authentication required.');
        }

        $preference = $this->service->updatePreferences($user->id, $validated);

        return response()->json(['status' => 'saved', 'data' => $preference]);
    }
}
