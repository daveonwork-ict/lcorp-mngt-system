<?php

namespace App\Http\Controllers;

use App\Models\ChatRoom;
use App\Services\ChatMessageService;
use App\Services\ChatReadService;
use App\Services\ChatRoomService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ChatReadController extends Controller
{
    public function __construct(
        private readonly ChatRoomService $roomService,
        private readonly ChatMessageService $messageService,
        private readonly ChatReadService $chatReadService,
    ) {
    }

    public function markRoomRead(Request $request, ChatRoom $room): RedirectResponse
    {
        $this->roomService->ensureMember($request->user(), $room);
        $this->messageService->markRoomRead($room, $request->user());

        return back()->with('status', 'Messages marked as read.');
    }

    public function unreadCount(Request $request, ChatRoom $room): JsonResponse
    {
        $this->roomService->ensureMember($request->user(), $room);

        return response()->json([
            'unread_count' => $this->chatReadService->unreadCountForRoom($room, $request->user()),
        ]);
    }
}
