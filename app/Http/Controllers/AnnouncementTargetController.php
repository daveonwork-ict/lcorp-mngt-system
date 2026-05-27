<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Services\AnnouncementTargetService;
use App\Services\CommunicationPermissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AnnouncementTargetController extends Controller
{
    public function __construct(
        private readonly AnnouncementTargetService $targetService,
        private readonly CommunicationPermissionService $permissionService,
    ) {
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $validated = $request->validate([
            'targets' => ['nullable', 'array'],
            'targets.*.target_type' => ['required_with:targets', 'string', 'max:100'],
            'targets.*.target_id' => ['nullable', 'integer'],
        ]);

        if (! $this->permissionService->canManageAnnouncement($request->user())) {
            abort(403, 'Target management is restricted.');
        }

        $this->targetService->syncTargets($announcement, $validated['targets'] ?? []);

        return back()->with('status', 'Announcement targets updated.');
    }
}
