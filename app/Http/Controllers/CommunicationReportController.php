<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AnnouncementRead;
use App\Models\ChatMessage;
use App\Models\ChatRoom;
use App\Services\ReportFilterService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommunicationReportController extends Controller
{
    public function __construct(private readonly ReportFilterService $filterService)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        if (! $user->hasPermission('view_communication_reports') && $user->role?->code !== config('rms.owner_role_code')) {
            abort(403, 'Communication report access denied.');
        }

        $filters = $this->filterService->normalize($request->all());

        $announcements = Announcement::query()
            ->with('creator')
            ->when($filters['date_from'], fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'], fn ($q, $date) => $q->whereDate('created_at', '<=', $date))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $engagement = Announcement::query()
            ->withCount([
                'reads as read_count' => fn ($q) => $q->whereIn('acknowledgment_status', ['read', 'acknowledged']),
                'reads as unread_count' => fn ($q) => $q->where('acknowledgment_status', 'unread'),
            ])
            ->latest('id')
            ->limit(30)
            ->get();

        $chatActivity = ChatMessage::query()
            ->with(['room', 'sender'])
            ->when($filters['date_from'], fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'], fn ($q, $date) => $q->whereDate('created_at', '<=', $date))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('reports.communication.index', [
            'filters' => $filters,
            'announcements' => $announcements,
            'engagement' => $engagement,
            'chatActivity' => $chatActivity,
            'unreadAnnouncementTotal' => AnnouncementRead::query()->where('acknowledgment_status', 'unread')->count(),
            'activeRooms' => ChatRoom::query()->where('status', 'active')->count(),
        ]);
    }
}
