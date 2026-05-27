<?php

namespace App\Services;

use App\Models\ChatRoom;
use App\Models\User;

class CommunicationPermissionService
{
    public function __construct(private readonly BranchAccessService $branchAccessService)
    {
    }

    public function isOwner(User $user): bool
    {
        return $user->role?->code === config('rms.owner_role_code');
    }

    public function canManageAnnouncement(User $user): bool
    {
        return $this->isOwner($user) || $user->hasPermission('publish_announcement') || $user->hasPermission('edit_announcement');
    }

    public function canAccessBranch(User $user, ?int $branchId): bool
    {
        return $this->branchAccessService->canAccessBranch($user, $branchId);
    }

    public function canAccessChatRoom(User $user, ChatRoom $room): bool
    {
        if ($this->isOwner($user)) {
            return true;
        }

        if ($room->branch_id && ! $this->canAccessBranch($user, $room->branch_id)) {
            return false;
        }

        return $room->members()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();
    }

    public function canManageRoomMembers(User $user, ChatRoom $room): bool
    {
        if ($this->isOwner($user) || $user->hasPermission('manage_chat_room_members')) {
            return true;
        }

        return $room->members()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->where('role_in_room', 'admin')
            ->exists();
    }
}
