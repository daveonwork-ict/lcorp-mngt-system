<?php

namespace Database\Seeders;

use App\Models\CommunicationNotification;
use App\Models\Notification;
use Illuminate\Database\Seeder;

class NormalizeCommunicationNotificationRoutesSeeder extends Seeder
{
    public function run(): void
    {
        CommunicationNotification::query()
            ->whereNotNull('chat_room_id')
            ->get()
            ->each(function (CommunicationNotification $notification): void {
                $payload = $notification->payload ?? [];
                $payload['route'] = '/chat/rooms/'.$notification->chat_room_id;

                $notification->update(['payload' => $payload]);
            });

        CommunicationNotification::query()
            ->whereNotNull('announcement_id')
            ->get()
            ->each(function (CommunicationNotification $notification): void {
                $payload = $notification->payload ?? [];
                $payload['route'] = '/announcements/'.$notification->announcement_id;

                $notification->update(['payload' => $payload]);
            });

        Notification::query()
            ->get()
            ->each(function (Notification $notification): void {
                $payload = $notification->payload ?? [];

                if (isset($payload['chat_room_id'])) {
                    $payload['route'] = '/chat/rooms/'.$payload['chat_room_id'];
                }

                if (isset($payload['announcement_id'])) {
                    $payload['route'] = '/announcements/'.$payload['announcement_id'];
                }

                if ($payload !== ($notification->payload ?? [])) {
                    $notification->update(['payload' => $payload]);
                }
            });
    }
}
