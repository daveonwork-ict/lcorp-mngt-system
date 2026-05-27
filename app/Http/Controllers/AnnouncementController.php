<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use App\Services\AnnouncementService;
use App\Services\AnnouncementTargetService;
use App\Services\CommunicationPermissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function __construct(
        private readonly AnnouncementService $announcementService,
        private readonly AnnouncementTargetService $targetService,
        private readonly CommunicationPermissionService $permissionService,
    ) {
    }

    public function index(Request $request): View
    {
        $this->announcementService->refreshStatuses();

        return view('communication.announcements.index', [
            'announcements' => $this->announcementService->paginateVisible($request->user(), $request->only(['status', 'priority_level', 'urgent_only'])),
            'filters' => $request->only(['status', 'priority_level', 'urgent_only']),
            'branches' => Branch::query()->where('is_active', true)->orderBy('branch_name')->get(),
            'roles' => Role::query()->orderBy('name')->get(),
            'users' => User::query()->orderBy('full_name')->limit(300)->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'announcement_type' => ['required', 'string', 'max:100'],
            'priority_level' => ['required', 'in:normal,important,urgent,critical'],
            'target_scope' => ['nullable', 'string', 'max:100'],
            'publish_start_at' => ['nullable', 'date'],
            'publish_end_at' => ['nullable', 'date', 'after_or_equal:publish_start_at'],
            'is_pinned' => ['nullable', 'boolean'],
            'is_urgent' => ['nullable', 'boolean'],
            'requires_acknowledgment' => ['nullable', 'boolean'],
            'status' => ['required', 'in:draft,scheduled,published,cancelled'],
            'targets' => ['nullable', 'array'],
            'targets.*.target_type' => ['required_with:targets', 'string', 'max:100'],
            'targets.*.target_id' => ['nullable', 'integer'],
        ]);

        if (! $this->permissionService->canManageAnnouncement($request->user())) {
            abort(403, 'Announcement creation is restricted.');
        }

        $announcement = $this->announcementService->create($validated);

        return redirect()->route('announcements.show', $announcement)->with('status', 'Announcement created.');
    }

    public function show(Request $request, Announcement $announcement): View
    {
        $visible = $this->targetService
            ->scopeVisibleToUser(Announcement::query()->whereKey($announcement->id), $request->user())
            ->exists();

        if (! $visible && ! $this->permissionService->isOwner($request->user())) {
            abort(403, 'You cannot view this announcement.');
        }

        return view('communication.announcements.show', [
            'announcement' => $announcement->load(['creator', 'approver', 'targets', 'attachments', 'reads.user.role', 'reads.branch']),
        ]);
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'announcement_type' => ['required', 'string', 'max:100'],
            'priority_level' => ['required', 'in:normal,important,urgent,critical'],
            'target_scope' => ['nullable', 'string', 'max:100'],
            'publish_start_at' => ['nullable', 'date'],
            'publish_end_at' => ['nullable', 'date', 'after_or_equal:publish_start_at'],
            'is_pinned' => ['nullable', 'boolean'],
            'is_urgent' => ['nullable', 'boolean'],
            'requires_acknowledgment' => ['nullable', 'boolean'],
            'status' => ['required', 'in:draft,scheduled,published,archived,cancelled'],
            'targets' => ['nullable', 'array'],
            'targets.*.target_type' => ['required_with:targets', 'string', 'max:100'],
            'targets.*.target_id' => ['nullable', 'integer'],
        ]);

        if (! $this->permissionService->canManageAnnouncement($request->user())) {
            abort(403, 'Announcement update is restricted.');
        }

        $this->announcementService->update($announcement, $validated);

        return back()->with('status', 'Announcement updated.');
    }

    public function publish(Request $request, Announcement $announcement): RedirectResponse
    {
        if (! $request->user()->hasPermission('publish_announcement') && ! $this->permissionService->isOwner($request->user())) {
            abort(403, 'Publish permission required.');
        }

        $this->announcementService->publish($announcement);

        return back()->with('status', 'Announcement publish status updated.');
    }

    public function archive(Request $request, Announcement $announcement): RedirectResponse
    {
        if (! $request->user()->hasPermission('archive_announcement') && ! $this->permissionService->isOwner($request->user())) {
            abort(403, 'Archive permission required.');
        }

        $this->announcementService->archive($announcement);

        return back()->with('status', 'Announcement archived.');
    }
}
