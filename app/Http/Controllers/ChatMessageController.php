<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\ChatRoom;
use App\Services\ChatAttachmentService;
use App\Services\ChatMessageService;
use App\Services\ChatRoomService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ChatMessageController extends Controller
{
    public function __construct(
        private readonly ChatRoomService $roomService,
        private readonly ChatMessageService $messageService,
        private readonly ChatAttachmentService $attachmentService,
    ) {
    }

    public function store(Request $request, ChatRoom $room): RedirectResponse
    {
        $this->roomService->ensureMember($request->user(), $room);

        if (! $request->user()->hasPermission('send_chat_message') && $request->user()->role?->code !== config('rms.owner_role_code')) {
            abort(403, 'Send chat message permission required.');
        }

        $validated = $request->validate([
            'message_body' => ['nullable', 'string'],
            'parent_message_id' => ['nullable', 'exists:chat_messages,id'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx', 'max:10240'],
        ]);

        $message = $this->messageService->send($room, $request->user(), [
            'message_body' => $validated['message_body'] ?? null,
            'parent_message_id' => $validated['parent_message_id'] ?? null,
            'has_attachment' => $request->hasFile('attachment'),
            'message_type' => $request->hasFile('attachment') ? 'file' : 'text',
        ]);

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('communication/chat');

            $this->attachmentService->create($message, [
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $file->getClientMimeType() ?: $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);
        }

        return back()->with('status', 'Message sent.');
    }

    public function update(Request $request, ChatMessage $message): RedirectResponse
    {
        $room = $message->room;
        if (! $room) {
            abort(404);
        }

        $this->roomService->ensureMember($request->user(), $room);

        $validated = $request->validate([
            'message_body' => ['required', 'string'],
        ]);

        $this->messageService->update($message, $request->user(), $validated);

        return back()->with('status', 'Message updated.');
    }

    public function destroy(Request $request, ChatMessage $message): RedirectResponse
    {
        $room = $message->room;
        if (! $room) {
            abort(404);
        }

        $this->roomService->ensureMember($request->user(), $room);
        $this->messageService->delete($message, $request->user());

        return back()->with('status', 'Message deleted.');
    }
}
