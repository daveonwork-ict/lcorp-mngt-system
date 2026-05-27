<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AnnouncementRead;
use App\Models\ChatMessage;
use App\Models\ChatRoomMember;
use App\Models\ChatRoom;
use App\Services\AnnouncementService;
use App\Services\AnnouncementTargetService;
use App\Services\ChatRoomService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommunicationDashboardController extends Controller
{
    public function __construct(
        private readonly AnnouncementService $announcementService,
        private readonly AnnouncementTargetService $targetService,
        private readonly ChatRoomService $chatRoomService,
        private readonly NotificationService $notificationService,
    ) {
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $announcementQuery = Announcement::query();
        $this->targetService->scopeVisibleToUser($announcementQuery, $user);

        $roomIds = $user->role?->code === config('rms.owner_role_code')
            ? ChatRoom::query()->pluck('id')->all()
            : ChatRoomMember::query()->where('user_id', $user->id)->where('status', 'active')->pluck('chat_room_id')->all();

        return view('communication.dashboard.index', [
            'activeAnnouncements' => (clone $announcementQuery)->whereIn('status', ['published', 'scheduled'])->count(),
            'urgentAnnouncements' => (clone $announcementQuery)->where('is_urgent', true)->where('status', 'published')->count(),
            'unreadAnnouncements' => (clone $announcementQuery)
                ->whereDoesntHave('reads', fn ($reads) => $reads->where('user_id', $user->id)->whereIn('acknowledgment_status', ['read', 'acknowledged']))
                ->count(),
            'activeChatRooms' => count($roomIds),
            'unreadMessages' => ChatMessage::query()
                ->whereIn('chat_room_id', $roomIds)
                ->where('sender_id', '!=', $user->id)
                ->whereNull('deleted_at')
                ->whereDoesntHave('reads', fn ($reads) => $reads->where('user_id', $user->id))
                ->count(),
            'pendingAcknowledgments' => AnnouncementRead::query()
                ->where('user_id', $user->id)
                ->where('acknowledgment_status', 'read')
                ->whereHas('announcement', fn ($a) => $a->where('requires_acknowledgment', true)->where('status', 'published'))
                ->count(),
            'recentAnnouncements' => (clone $announcementQuery)->with('creator')->latest('published_at')->limit(8)->get(),
            'recentMessages' => ChatMessage::query()->with(['sender', 'room'])->whereIn('chat_room_id', $roomIds)->latest('id')->limit(8)->get(),
            'urgentNotices' => (clone $announcementQuery)->where('is_urgent', true)->where('status', 'published')->latest('published_at')->limit(8)->get(),
            'latestRooms' => ChatRoom::query()->whereIn('id', $roomIds)->latest('updated_at')->limit(8)->get(),
            'unreadUsersByAnnouncement' => Announcement::query()
                ->whereIn('id', (clone $announcementQuery)->pluck('id'))
                ->withCount(['reads as unread_count' => fn ($reads) => $reads->where('acknowledgment_status', 'unread')])
                ->latest('published_at')
                ->limit(6)
                ->get(),
            'notificationUnread' => $this->notificationService->communicationUnreadCount($user),
        ]);
    }
}
