<?php

namespace App\Services;

use App\Models\ChatMessage;
use App\Models\ChatMessageRead;
use App\Models\ChatRoom;
use App\Models\User;

class ChatReadService
{
    public function markRoomAsRead(ChatRoom $room, User $user): void
    {
        $messages = ChatMessage::query()
            ->where('chat_room_id', $room->id)
            ->whereNull('deleted_at')
            ->where('sender_id', '!=', $user->id)
            ->pluck('id');

        foreach ($messages as $messageId) {
            ChatMessageRead::query()->updateOrCreate(
                [
                    'chat_message_id' => $messageId,
                    'user_id' => $user->id,
                ],
                ['read_at' => now()]
            );
        }
    }

    public function unreadCountForRoom(ChatRoom $room, User $user): int
    {
        return ChatMessage::query()
            ->where('chat_room_id', $room->id)
            ->whereNull('deleted_at')
            ->where('sender_id', '!=', $user->id)
            ->whereDoesntHave('reads', fn ($reads) => $reads->where('user_id', $user->id))
            ->count();
    }
}
