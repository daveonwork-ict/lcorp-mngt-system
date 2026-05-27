<?php

namespace App\Services;

use App\Models\CommunicationNotification;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class NotificationService
{
    public function create(?int $userId, ?int $branchId, string $title, string $message, string $type = 'info', array $payload = []): Notification
    {
        return Notification::query()->create([
            'user_id' => $userId,
            'branch_id' => $branchId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'is_read' => false,
            'payload' => $payload ?: null,
        ]);
    }

    public function paginateForUser(int $userId): LengthAwarePaginator
    {
        return Notification::query()
            ->where(function ($query) use ($userId): void {
                $query->where('user_id', $userId)
                    ->orWhereNull('user_id');
            })
            ->latest('id')
            ->paginate(15);
    }

    public function unreadCount(int $userId): int
    {
        return Notification::query()
            ->where(function ($query) use ($userId): void {
                $query->where('user_id', $userId)
                    ->orWhereNull('user_id');
            })
            ->where('is_read', false)
            ->count();
    }

    public function markRead(Notification $notification): void
    {
        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    public function markAllRead(int $userId): void
    {
        Notification::query()
            ->where(function ($query) use ($userId): void {
                $query->where('user_id', $userId)
                    ->orWhereNull('user_id');
            })
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    public function createCommunicationForUsers(
        Collection|array $userIds,
        ?int $branchId,
        string $category,
        string $title,
        string $message,
        array $payload = [],
        ?int $announcementId = null,
        ?int $chatRoomId = null,
        ?int $chatMessageId = null
    ): void
    {
        $ids = collect($userIds)->filter()->unique()->values();

        foreach ($ids as $userId) {
            CommunicationNotification::query()->create([
                'user_id' => $userId,
                'branch_id' => $branchId,
                'announcement_id' => $announcementId,
                'chat_room_id' => $chatRoomId,
                'chat_message_id' => $chatMessageId,
                'category' => $category,
                'title' => $title,
                'message' => $message,
                'payload' => $payload ?: null,
                'is_read' => false,
                'created_by' => auth()->id(),
            ]);

            // Keep existing bell integration intact via legacy notifications table.
            $this->create((int) $userId, $branchId, $title, $message, $category, $payload);
        }
    }

    public function paginateCommunicationForUser(User $user): LengthAwarePaginator
    {
        return CommunicationNotification::query()
            ->with(['announcement', 'room', 'messageRecord'])
            ->where(function (Builder $query) use ($user): void {
                $query->where('user_id', $user->id)
                    ->orWhereNull('user_id');
            })
            ->when($user->role?->code !== config('rms.owner_role_code'), function (Builder $query) use ($user): void {
                $branchIds = $user->branches()->pluck('branches.id')->all();
                if (! empty($branchIds)) {
                    $query->where(function (Builder $scope) use ($branchIds): void {
                        $scope->whereNull('branch_id')->orWhereIn('branch_id', $branchIds);
                    });
                }
            })
            ->latest('id')
            ->paginate(20);
    }

    public function markCommunicationRead(CommunicationNotification $notification, User $user): void
    {
        if ($notification->user_id !== null && $notification->user_id !== $user->id && $user->role?->code !== config('rms.owner_role_code')) {
            abort(403, 'Notification access denied.');
        }

        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    public function markAllCommunicationRead(User $user): void
    {
        CommunicationNotification::query()
            ->where(function (Builder $query) use ($user): void {
                $query->where('user_id', $user->id)
                    ->orWhereNull('user_id');
            })
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    public function communicationUnreadCount(User $user): int
    {
        return CommunicationNotification::query()
            ->where(function (Builder $query) use ($user): void {
                $query->where('user_id', $user->id)
                    ->orWhereNull('user_id');
            })
            ->where('is_read', false)
            ->count();
    }
}
