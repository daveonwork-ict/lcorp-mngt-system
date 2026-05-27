<?php

namespace App\Http\Controllers;

use App\Services\PushNotificationPreparationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function __construct(private readonly PushNotificationPreparationService $service)
    {
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'string'],
            'public_key' => ['nullable', 'string'],
            'auth_token' => ['nullable', 'string'],
            'subscription_payload' => ['nullable', 'array'],
        ]);

        $user = $request->user();
        if (! $user) {
            abort(403, 'Authentication required.');
        }

        $subscription = $this->service->upsertSubscription($validated + [
            'user_id' => $user->id,
            'branch_id' => $user->primary_branch_id,
        ]);

        return response()->json(['status' => 'saved', 'id' => $subscription->id]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'string'],
        ]);

        $user = $request->user();
        if (! $user) {
            abort(403, 'Authentication required.');
        }

        $this->service->removeSubscription($validated['endpoint'], $user->id);

        return response()->json(['status' => 'removed']);
    }
}
