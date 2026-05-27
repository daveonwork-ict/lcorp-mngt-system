<?php

namespace App\Http\Controllers;

use App\Models\ChatMessageAttachment;
use App\Services\ChatRoomService;
use App\Services\FileAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatAttachmentController extends Controller
{
    public function __construct(
        private readonly ChatRoomService $roomService,
        private readonly FileAccessService $fileAccessService,
    ) {
    }

    public function download(Request $request, ChatMessageAttachment $attachment): StreamedResponse|RedirectResponse
    {
        $message = $attachment->message;
        $room = $message?->room;

        if (! $message || ! $room) {
            abort(404);
        }

        $this->roomService->ensureMember($request->user(), $room);

        return $this->fileAccessService->download(
            'communication',
            $attachment->file_path,
            $attachment->file_name,
            $room->branch_id ?? null,
            'chat_attachment',
            $attachment->id
        );
    }
}
