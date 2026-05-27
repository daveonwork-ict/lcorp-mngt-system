<?php

namespace App\Services;

use App\Models\ChatRoom;
use App\Models\ChatRoomMember;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ChatRoomService
{
    public function __construct(
        private readonly CommunicationPermissionService $permissionService,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    public function paginateForUser(User $user): LengthAwarePaginator
    {
        $query = ChatRoom::query()
            ->with(['branch', 'creator', 'members.user'])
            ->latest('updated_at');

        if (! $this->permissionService->isOwner($user)) {
            $query->whereHas('members', function ($members) use ($user): void {
                $members->where('user_id', $user->id)->where('status', 'active');
            });
        }

        return $query->paginate(20);
    }

    public function create(array $payload): ChatRoom
    {
        $room = ChatRoom::query()->create([
            'room_number' => $payload['room_number'] ?? ('ROOM-'.now()->format('YmdHis').'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT)),
            'room_name' => $payload['room_name'],
            'room_type' => strtolower($payload['room_type']),
            'branch_id' => $payload['branch_id'] ?? null,
            'created_by' => auth()->id(),
            'status' => 'active',
        ]);

        $memberIds = collect($payload['member_ids'] ?? [])->filter()->map(fn ($id) => (int) $id)->unique();
        $memberIds->push((int) auth()->id());

        foreach ($memberIds->unique() as $memberId) {
            ChatRoomMember::query()->updateOrCreate(
                [
                    'chat_room_id' => $room->id,
                    'user_id' => $memberId,
                ],
                [
                    'role_in_room' => $memberId === auth()->id() ? 'admin' : 'member',
                    'joined_at' => now(),
                    'status' => 'active',
                ]
            );
        }

        $this->auditLogService->record('communication', 'chat_room_created', [], $room->toArray(), $room->branch_id, 'Chat room created');

        return $room;
    }

    public function update(ChatRoom $room, array $payload): ChatRoom
    {
        $before = $room->toArray();

        $room->update([
            'room_name' => $payload['room_name'] ?? $room->room_name,
            'status' => $payload['status'] ?? $room->status,
        ]);

        $this->auditLogService->record('communication', 'chat_room_updated', $before, $room->toArray(), $room->branch_id, 'Chat room updated');

        return $room;
    }

    public function archive(ChatRoom $room): ChatRoom
    {
        $before = $room->toArray();

        $room->update([
            'status' => 'archived',
            'archived_at' => now(),
        ]);

        $this->auditLogService->record('communication', 'chat_room_archived', $before, $room->toArray(), $room->branch_id, 'Chat room archived');

        return $room;
    }

    public function addMember(ChatRoom $room, int $userId, string $roleInRoom = 'member'): ChatRoomMember
    {
        $member = ChatRoomMember::query()->updateOrCreate(
            [
                'chat_room_id' => $room->id,
                'user_id' => $userId,
            ],
            [
                'role_in_room' => strtolower($roleInRoom),
                'joined_at' => now(),
                'status' => 'active',
            ]
        );

        $this->auditLogService->record('communication', 'chat_member_added', [], $member->toArray(), $room->branch_id, 'Chat room member added');

        return $member;
    }

    public function removeMember(ChatRoom $room, int $userId): void
    {
        $member = $room->members()->where('user_id', $userId)->first();
        if (! $member) {
            return;
        }

        $before = $member->toArray();
        $member->update(['status' => 'inactive']);

        $this->auditLogService->record('communication', 'chat_member_removed', $before, $member->toArray(), $room->branch_id, 'Chat room member removed');
    }

    public function ensureMember(User $user, ChatRoom $room): void
    {
        if (! $this->permissionService->canAccessChatRoom($user, $room)) {
            abort(403, 'Chat room access denied.');
        }
    }
}
