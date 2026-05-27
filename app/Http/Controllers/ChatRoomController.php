<?php

namespace App\Http\Controllers;

use App\Models\ChatRoom;
use App\Models\User;
use App\Services\ChatMessageService;
use App\Services\ChatReadService;
use App\Services\ChatRoomService;
use App\Services\CommunicationPermissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatRoomController extends Controller
{
    public function __construct(
        private readonly ChatRoomService $roomService,
        private readonly ChatMessageService $messageService,
        private readonly ChatReadService $chatReadService,
        private readonly CommunicationPermissionService $permissionService,
    ) {
    }

    public function index(Request $request): View
    {
        return view('communication.chat.index', [
            'rooms' => $this->roomService->paginateForUser($request->user()),
            'users' => User::query()->orderBy('full_name')->limit(300)->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'room_name' => ['required', 'string', 'max:255'],
            'room_type' => ['required', 'in:private,branch,management,inventory,accounting,operations,custom'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'member_ids' => ['nullable', 'array'],
            'member_ids.*' => ['integer', 'exists:users,id'],
        ]);

        if (! $request->user()->hasPermission('create_chat_room') && ! $this->permissionService->isOwner($request->user())) {
            abort(403, 'Create chat room permission required.');
        }

        if (($validated['room_type'] ?? null) === 'branch' && empty($validated['branch_id'])) {
            return back()->withErrors(['branch_id' => 'Branch room requires branch.']);
        }

        if (! empty($validated['branch_id']) && ! $this->permissionService->canAccessBranch($request->user(), (int) $validated['branch_id'])) {
            abort(403, 'Branch access denied.');
        }

        $room = $this->roomService->create($validated);

        return redirect()->route('chat.rooms.show', $room)->with('status', 'Chat room created.');
    }

    public function show(Request $request, ChatRoom $room): View
    {
        $this->roomService->ensureMember($request->user(), $room);
        $this->messageService->markRoomRead($room, $request->user());

        return view('communication.chat.show', [
            'room' => $room->load(['branch', 'members.user', 'messages.sender', 'messages.parent', 'messages.attachments', 'messages.reads']),
            'unreadCount' => $this->chatReadService->unreadCountForRoom($room, $request->user()),
        ]);
    }

    public function update(Request $request, ChatRoom $room): RedirectResponse
    {
        if (! $this->permissionService->canManageRoomMembers($request->user(), $room)) {
            abort(403, 'Chat room update permission required.');
        }

        $validated = $request->validate([
            'room_name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:active,archived,disabled'],
        ]);

        $this->roomService->update($room, $validated);

        return back()->with('status', 'Chat room updated.');
    }

    public function archive(Request $request, ChatRoom $room): RedirectResponse
    {
        if (! $this->permissionService->canManageRoomMembers($request->user(), $room)) {
            abort(403, 'Chat room archive permission required.');
        }

        $this->roomService->archive($room);

        return back()->with('status', 'Chat room archived.');
    }
}
