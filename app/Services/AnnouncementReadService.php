<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\AnnouncementRead;
use App\Models\User;

class AnnouncementReadService
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function markRead(Announcement $announcement, User $user): AnnouncementRead
    {
        $record = AnnouncementRead::query()->updateOrCreate(
            [
                'announcement_id' => $announcement->id,
                'user_id' => $user->id,
            ],
            [
                'branch_id' => $user->primary_branch_id,
                'read_at' => now(),
                'acknowledgment_status' => 'read',
            ]
        );

        $this->auditLogService->record('communication', 'announcement_read', [], $record->toArray(), $record->branch_id, 'Announcement marked as read');

        return $record;
    }

    public function acknowledge(Announcement $announcement, User $user): AnnouncementRead
    {
        $record = AnnouncementRead::query()->updateOrCreate(
            [
                'announcement_id' => $announcement->id,
                'user_id' => $user->id,
            ],
            [
                'branch_id' => $user->primary_branch_id,
                'read_at' => now(),
                'acknowledgment_status' => 'acknowledged',
            ]
        );

        $this->auditLogService->record('communication', 'announcement_acknowledged', [], $record->toArray(), $record->branch_id, 'Urgent announcement acknowledged');

        return $record;
    }
}
