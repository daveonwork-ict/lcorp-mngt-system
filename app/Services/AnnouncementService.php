<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AnnouncementService
{
    public function __construct(
        private readonly AnnouncementTargetService $targetService,
        private readonly NotificationService $notificationService,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    public function paginateVisible(User $user, array $filters = []): LengthAwarePaginator
    {
        $query = Announcement::query()
            ->with(['creator', 'targets', 'attachments'])
            ->latest('is_pinned')
            ->latest('published_at')
            ->latest('id');

        $this->targetService->scopeVisibleToUser($query, $user);

        $query->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status));
        $query->when($filters['priority_level'] ?? null, fn ($q, $priority) => $q->where('priority_level', $priority));
        $query->when($filters['urgent_only'] ?? null, fn ($q) => $q->where('is_urgent', true));

        if (($filters['active_only'] ?? true) && ($filters['status'] ?? null) === null) {
            $query->where(function ($active): void {
                $active->whereIn('status', ['published', 'scheduled'])
                    ->where(function ($window): void {
                        $window->whereNull('publish_start_at')
                            ->orWhere('publish_start_at', '<=', now());
                    })
                    ->where(function ($window): void {
                        $window->whereNull('publish_end_at')
                            ->orWhere('publish_end_at', '>=', now());
                    });
            });
        }

        return $query->paginate(20)->withQueryString();
    }

    public function create(array $payload): Announcement
    {
        $announcement = Announcement::query()->create([
            'announcement_number' => $payload['announcement_number'] ?? ('ANN-'.now()->format('YmdHis').'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT)),
            'title' => $payload['title'],
            'content' => $payload['content'],
            'announcement_type' => $payload['announcement_type'],
            'priority_level' => strtolower($payload['priority_level'] ?? 'normal'),
            'target_scope' => $payload['target_scope'] ?? 'custom',
            'publish_start_at' => $payload['publish_start_at'] ?? null,
            'publish_end_at' => $payload['publish_end_at'] ?? null,
            'is_pinned' => (bool) ($payload['is_pinned'] ?? false),
            'is_urgent' => (bool) ($payload['is_urgent'] ?? false),
            'requires_acknowledgment' => (bool) ($payload['requires_acknowledgment'] ?? false),
            'status' => $payload['status'] ?? 'draft',
            'created_by' => auth()->id(),
        ]);

        $this->targetService->syncTargets($announcement, $payload['targets'] ?? []);

        if ($announcement->status === 'published') {
            $announcement->update([
                'published_at' => now(),
                'approved_by' => auth()->id(),
            ]);
            $this->dispatchPublicationNotifications($announcement);
        }

        $this->auditLogService->record('communication', 'announcement_created', [], $announcement->toArray(), null, 'Announcement created');

        return $announcement;
    }

    public function update(Announcement $announcement, array $payload): Announcement
    {
        $before = $announcement->toArray();

        $announcement->update([
            'title' => $payload['title'],
            'content' => $payload['content'],
            'announcement_type' => $payload['announcement_type'],
            'priority_level' => strtolower($payload['priority_level'] ?? $announcement->priority_level),
            'target_scope' => $payload['target_scope'] ?? $announcement->target_scope,
            'publish_start_at' => $payload['publish_start_at'] ?? $announcement->publish_start_at,
            'publish_end_at' => $payload['publish_end_at'] ?? $announcement->publish_end_at,
            'is_pinned' => (bool) ($payload['is_pinned'] ?? false),
            'is_urgent' => (bool) ($payload['is_urgent'] ?? false),
            'requires_acknowledgment' => (bool) ($payload['requires_acknowledgment'] ?? false),
            'status' => $payload['status'] ?? $announcement->status,
        ]);

        if (array_key_exists('targets', $payload)) {
            $this->targetService->syncTargets($announcement, $payload['targets'] ?? []);
        }

        if ($announcement->status === 'published' && ! $announcement->published_at) {
            $announcement->update([
                'published_at' => now(),
                'approved_by' => auth()->id(),
            ]);
            $this->dispatchPublicationNotifications($announcement);
        }

        $this->auditLogService->record('communication', 'announcement_updated', $before, $announcement->toArray(), null, 'Announcement updated');

        return $announcement;
    }

    public function publish(Announcement $announcement): Announcement
    {
        $before = $announcement->toArray();

        $scheduled = $announcement->publish_start_at && $announcement->publish_start_at->isFuture();

        $announcement->update([
            'status' => $scheduled ? 'scheduled' : 'published',
            'published_at' => $scheduled ? null : now(),
            'approved_by' => auth()->id(),
        ]);

        if (! $scheduled) {
            $this->dispatchPublicationNotifications($announcement);
        }

        $this->auditLogService->record('communication', 'announcement_published', $before, $announcement->toArray(), null, 'Announcement published/scheduled');

        return $announcement;
    }

    public function archive(Announcement $announcement): Announcement
    {
        $before = $announcement->toArray();

        $announcement->update([
            'status' => 'archived',
            'archived_at' => now(),
        ]);

        $this->auditLogService->record('communication', 'announcement_archived', $before, $announcement->toArray(), null, 'Announcement archived');

        return $announcement;
    }

    public function refreshStatuses(): void
    {
        Announcement::query()
            ->where('status', 'scheduled')
            ->whereNotNull('publish_start_at')
            ->where('publish_start_at', '<=', now())
            ->get()
            ->each(function (Announcement $announcement): void {
                $announcement->update([
                    'status' => 'published',
                    'published_at' => now(),
                ]);
                $this->dispatchPublicationNotifications($announcement);
            });

        Announcement::query()
            ->where('status', 'published')
            ->whereNotNull('publish_end_at')
            ->where('publish_end_at', '<', now())
            ->update(['status' => 'expired']);
    }

    private function dispatchPublicationNotifications(Announcement $announcement): void
    {
        $userIds = $this->targetService->resolveTargetUserIds($announcement);
        $title = $announcement->is_urgent ? 'Urgent announcement' : 'New announcement';

        $this->notificationService->createCommunicationForUsers(
            $userIds,
            null,
            'announcement',
            $title,
            $announcement->title,
            [
                'announcement_id' => $announcement->id,
                'route' => route('announcements.show', $announcement),
            ],
            $announcement->id,
            null,
            null
        );

        if ($announcement->is_urgent) {
            $this->notificationService->createCommunicationForUsers(
                $userIds,
                null,
                'urgent_announcement',
                'Urgent notice',
                $announcement->title,
                ['announcement_id' => $announcement->id],
                $announcement->id,
                null,
                null
            );
        }
    }
}
