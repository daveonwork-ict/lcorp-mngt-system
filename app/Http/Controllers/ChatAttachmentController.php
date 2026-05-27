<?php

namespace App\Http\Controllers;

use App\Models\ChatMessageAttachment;
use App\Services\ChatRoomService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatAttachmentController extends Controller
{
    public function __construct(private readonly ChatRoomService $roomService)
    {
    }

    public function download(Request $request, ChatMessageAttachment $attachment): StreamedResponse|RedirectResponse
    {
        $message = $attachment->message;
        $room = $message?->room;

        if (! $message || ! $room) {
            abort(404);
        }

        $this->roomService->ensureMember($request->user(), $room);

        if (! Storage::exists($attachment->file_path)) {
            return back()->withErrors(['file' => 'File not found.']);
        }

        return Storage::download($attachment->file_path, $attachment->file_name);
    }
}
