<?php

namespace App\Http\Controllers;

use App\Models\ChatRoom;
use App\Services\ChatRoomService;
use App\Services\CommunicationPermissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatRoomMemberController extends Controller
{
    public function __construct(
        private readonly ChatRoomService $roomService,
        private readonly CommunicationPermissionService $permissionService,
    ) {
    }

    public function index(Request $request, ChatRoom $room): View
    {
        $this->roomService->ensureMember($request->user(), $room);

        return view('communication.chat.members', [
            'room' => $room->load(['members.user.role']),
            'users' => \App\Models\User::query()->orderBy('full_name')->limit(300)->get(),
        ]);
    }

    public function store(Request $request, ChatRoom $room): RedirectResponse
    {
        if (! $this->permissionService->canManageRoomMembers($request->user(), $room)) {
            abort(403, 'Member management permission required.');
        }

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'role_in_room' => ['required', 'in:admin,member,viewer'],
        ]);

        $this->roomService->addMember($room, (int) $validated['user_id'], $validated['role_in_room']);

        return back()->with('status', 'Member added/updated.');
    }

    public function destroy(Request $request, ChatRoom $room, int $userId): RedirectResponse
    {
        if (! $this->permissionService->canManageRoomMembers($request->user(), $room)) {
            abort(403, 'Member management permission required.');
        }

        $this->roomService->removeMember($room, $userId);

        return back()->with('status', 'Member removed.');
    }
}
