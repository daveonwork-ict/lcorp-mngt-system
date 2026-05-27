<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Services\AnnouncementReadService;
use App\Services\AnnouncementTargetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnnouncementReadController extends Controller
{
    public function __construct(
        private readonly AnnouncementReadService $readService,
        private readonly AnnouncementTargetService $targetService,
    ) {
    }

    public function index(Request $request, Announcement $announcement): View
    {
        if (! $request->user()->hasPermission('view_announcement_reads') && $request->user()->role?->code !== config('rms.owner_role_code')) {
            abort(403, 'Read tracking access denied.');
        }

        $reads = $announcement->reads()
            ->with(['user.role', 'branch'])
            ->when($request->filled('branch_id'), fn ($q) => $q->where('branch_id', $request->integer('branch_id')))
            ->when($request->filled('role_id'), fn ($q) => $q->whereHas('user', fn ($u) => $u->where('role_id', $request->integer('role_id'))))
            ->orderByDesc('read_at')
            ->paginate(20)
            ->withQueryString();

        return view('communication.announcements.reads', [
            'announcement' => $announcement,
            'reads' => $reads,
            'filters' => $request->only(['branch_id', 'role_id']),
        ]);
    }

    public function markRead(Request $request, Announcement $announcement): RedirectResponse
    {
        $visible = $this->targetService
            ->scopeVisibleToUser(\App\Models\Announcement::query()->whereKey($announcement->id), $request->user())
            ->exists();

        if (! $visible) {
            abort(403, 'Announcement access denied.');
        }

        $this->readService->markRead($announcement, $request->user());

        return back()->with('status', 'Announcement marked as read.');
    }

    public function acknowledge(Request $request, Announcement $announcement): RedirectResponse
    {
        $visible = $this->targetService
            ->scopeVisibleToUser(\App\Models\Announcement::query()->whereKey($announcement->id), $request->user())
            ->exists();

        if (! $visible) {
            abort(403, 'Announcement access denied.');
        }

        $this->readService->acknowledge($announcement, $request->user());

        return back()->with('status', 'Announcement acknowledged.');
    }
}
