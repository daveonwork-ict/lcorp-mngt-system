<?php

namespace App\Services;

use App\Models\ChatMessage;
use App\Models\ChatRoom;
use App\Models\User;

class ChatMessageService
{
    public function __construct(
        private readonly ChatReadService $chatReadService,
        private readonly NotificationService $notificationService,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    public function send(ChatRoom $room, User $sender, array $payload): ChatMessage
    {
        if ($room->status !== 'active') {
            abort(422, 'Archived or disabled rooms cannot accept new messages.');
        }

        $body = trim((string) ($payload['message_body'] ?? ''));
        $hasAttachment = (bool) ($payload['has_attachment'] ?? false);

        if ($body === '' && ! $hasAttachment) {
            abort(422, 'Message body is required when no attachment is provided.');
        }

        $message = ChatMessage::query()->create([
            'chat_room_id' => $room->id,
            'sender_id' => $sender->id,
            'branch_id' => $room->branch_id,
            'message_body' => $body !== '' ? $body : null,
            'message_type' => strtolower((string) ($payload['message_type'] ?? ($hasAttachment ? 'file' : 'text'))),
            'parent_message_id' => $payload['parent_message_id'] ?? null,
            'status' => 'sent',
        ]);

        $this->dispatchMessageNotifications($room, $sender, $message);
        $this->auditLogService->record('communication', 'chat_message_sent', [], $message->toArray(), $room->branch_id, 'Chat message sent');

        return $message;
    }

    public function update(ChatMessage $message, User $user, array $payload): ChatMessage
    {
        if ($message->sender_id !== $user->id && ! $user->hasPermission('edit_chat_message')) {
            abort(403, 'You cannot edit this message.');
        }

        $before = $message->toArray();

        $message->update([
            'message_body' => trim((string) ($payload['message_body'] ?? $message->message_body)),
            'edited_at' => now(),
            'status' => 'edited',
        ]);

        $this->auditLogService->record('communication', 'chat_message_edited', $before, $message->toArray(), $message->branch_id, 'Chat message edited');

        return $message;
    }

    public function delete(ChatMessage $message, User $user): ChatMessage
    {
        if ($message->sender_id !== $user->id && ! $user->hasPermission('delete_chat_message')) {
            abort(403, 'You cannot delete this message.');
        }

        $before = $message->toArray();

        $message->update([
            'deleted_at' => now(),
            'status' => 'deleted',
        ]);

        $this->auditLogService->record('communication', 'chat_message_deleted', $before, $message->toArray(), $message->branch_id, 'Chat message deleted');

        return $message;
    }

    public function markRoomRead(ChatRoom $room, User $user): void
    {
        $this->chatReadService->markRoomAsRead($room, $user);
    }

    private function dispatchMessageNotifications(ChatRoom $room, User $sender, ChatMessage $message): void
    {
        $recipientIds = $room->members()
            ->where('status', 'active')
            ->where('user_id', '!=', $sender->id)
            ->pluck('user_id');

        if ($recipientIds->isNotEmpty()) {
            $category = $room->room_type === 'private' ? 'private_message' : 'group_message';
            $title = $room->room_type === 'private' ? 'New private message' : 'New chat message';

            $this->notificationService->createCommunicationForUsers(
                $recipientIds,
                $room->branch_id,
                $category,
                $title,
                $sender->display_name.': '.str((string) $message->message_body)->limit(120),
                [
                    'chat_room_id' => $room->id,
                    'chat_message_id' => $message->id,
                    'route' => route('chat.rooms.show', $room, false),
                ],
                null,
                $room->id,
                $message->id
            );
        }

        $mentions = collect((array) preg_match_all('/@([A-Za-z0-9._-]+)/', (string) $message->message_body, $matches) ? $matches[1] : [])
            ->unique()
            ->values();

        if ($mentions->isNotEmpty()) {
            $mentionedUsers = User::query()
                ->whereIn('username', $mentions)
                ->whereIn('id', $recipientIds)
                ->pluck('id');

            if ($mentionedUsers->isNotEmpty()) {
                $this->notificationService->createCommunicationForUsers(
                    $mentionedUsers,
                    $room->branch_id,
                    'mention',
                    'You were mentioned',
                    $sender->display_name.' mentioned you in '.$room->room_name,
                    [
                        'chat_room_id' => $room->id,
                        'chat_message_id' => $message->id,
                        'route' => route('chat.rooms.show', $room, false),
                    ],
                    null,
                    $room->id,
                    $message->id
                );
            }
        }
    }
}
