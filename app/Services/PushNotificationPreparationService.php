<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\UserDevicePreference;

class PushNotificationPreparationService
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function upsertSubscription(array $payload): PushSubscription
    {
        $subscription = PushSubscription::query()->updateOrCreate(
            ['endpoint' => $payload['endpoint']],
            [
                'user_id' => $payload['user_id'],
                'branch_id' => $payload['branch_id'] ?? null,
                'public_key' => $payload['public_key'] ?? null,
                'auth_token' => $payload['auth_token'] ?? null,
                'subscription_payload' => $payload['subscription_payload'] ?? null,
                'is_active' => true,
                'last_used_at' => now(),
            ]
        );

        $this->auditLogService->record('pwa', 'push_subscription_created', [], $subscription->toArray(), $subscription->branch_id, 'Push subscription created', $subscription->user_id);

        return $subscription;
    }

    public function removeSubscription(string $endpoint, int $userId): void
    {
        $subscription = PushSubscription::query()->where('endpoint', $endpoint)->where('user_id', $userId)->first();
        if (! $subscription) {
            return;
        }

        $before = $subscription->toArray();
        $subscription->update(['is_active' => false]);

        $this->auditLogService->record('pwa', 'push_subscription_removed', $before, $subscription->fresh()->toArray(), $subscription->branch_id, 'Push subscription removed', $userId);
    }

    public function updatePreferences(int $userId, array $payload): UserDevicePreference
    {
        $pref = UserDevicePreference::query()->updateOrCreate(
            ['user_id' => $userId],
            [
                'preferences' => $payload['preferences'] ?? null,
                'allow_low_stock' => (bool) ($payload['allow_low_stock'] ?? true),
                'allow_cash_variance' => (bool) ($payload['allow_cash_variance'] ?? true),
                'allow_pending_approval' => (bool) ($payload['allow_pending_approval'] ?? true),
                'allow_announcement' => (bool) ($payload['allow_announcement'] ?? true),
                'allow_chat_message' => (bool) ($payload['allow_chat_message'] ?? true),
                'allow_low_wallet' => (bool) ($payload['allow_low_wallet'] ?? true),
                'allow_warranty_update' => (bool) ($payload['allow_warranty_update'] ?? true),
                'allow_daily_closing' => (bool) ($payload['allow_daily_closing'] ?? true),
            ]
        );

        $this->auditLogService->record('pwa', 'device_preference_updated', [], $pref->toArray(), auth()->user()?->primary_branch_id, 'Device preference updated', $userId);

        return $pref;
    }

    public function readinessCatalog(): array
    {
        return [
            'low_stock',
            'cash_variance',
            'pending_approval',
            'announcement',
            'chat_message',
            'low_wallet',
            'warranty_update',
            'daily_closing',
        ];
    }
}
