<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AnnouncementAttachment;
use App\Services\AuditLogService;
use App\Services\CommunicationPermissionService;
use App\Services\FileAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnnouncementAttachmentController extends Controller
{
    public function __construct(
        private readonly CommunicationPermissionService $permissionService,
        private readonly AuditLogService $auditLogService,
        private readonly FileAccessService $fileAccessService,
    ) {
    }

    public function store(Request $request, Announcement $announcement): RedirectResponse
    {
        $validated = $request->validate([
            'attachment' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx', 'max:10240'],
        ]);

        if (! $this->permissionService->canManageAnnouncement($request->user())) {
            abort(403, 'Attachment upload is restricted.');
        }

        $file = $validated['attachment'];
        $path = $file->store('communication/announcements');

        $attachment = $announcement->attachments()->create([
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $file->getClientMimeType() ?: $file->getMimeType(),
            'file_size' => $file->getSize(),
            'uploaded_by' => $request->user()->id,
        ]);

        $this->auditLogService->record('communication', 'announcement_attachment_uploaded', [], $attachment->toArray(), null, 'Announcement attachment uploaded');

        return back()->with('status', 'Attachment uploaded.');
    }

    public function download(Request $request, AnnouncementAttachment $attachment): StreamedResponse|RedirectResponse
    {
        $announcement = $attachment->announcement()->with('targets')->first();
        if (! $announcement) {
            abort(404);
        }

        if (! $this->permissionService->isOwner($request->user())) {
            $isVisible = app(\App\Services\AnnouncementTargetService::class)
                ->scopeVisibleToUser(\App\Models\Announcement::query()->whereKey($announcement->id), $request->user())
                ->exists();

            if (! $isVisible) {
                abort(403, 'Attachment access denied.');
            }
        }

        return $this->fileAccessService->download(
            'communication',
            $attachment->file_path,
            $attachment->file_name,
            $announcement->branch_id ?? null,
            'announcement_attachment',
            $attachment->id
        );
    }
}
