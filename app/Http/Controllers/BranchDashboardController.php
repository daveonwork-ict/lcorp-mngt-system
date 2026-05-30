<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AttendanceLog;
use App\Models\Branch;
use App\Models\ChatMessage;
use App\Models\ChatRoomMember;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\Payslip;
use App\Services\AnnouncementTargetService;
use App\Services\AuditLogService;
use App\Services\DashboardAnalyticsService;
use App\Services\NotificationService;
use App\Services\ReportFilterService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BranchDashboardController extends Controller
{
    public function __construct(
        private readonly DashboardAnalyticsService $dashboardService,
        private readonly ReportFilterService $filterService,
        private readonly AuditLogService $auditLogService,
        private readonly AnnouncementTargetService $announcementTargetService,
        private readonly NotificationService $notificationService,
    ) {
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        if (! $user->hasPermission('view_branch_dashboard') && $user->role?->code !== config('rms.owner_role_code')) {
            abort(403, 'Branch dashboard access denied.');
        }

        $filters = $this->filterService->normalize($request->only(['date_from', 'date_to', 'branch_id', 'category_id', 'provider_id']));
        $branchId = $this->filterService->enforceBranchScope($user, $filters['branch_id']) ?? (int) $request->session()->get('active_branch_id');

        if (! $branchId) {
            abort(422, 'Branch context is required.');
        }

        $branch = Branch::query()->findOrFail($branchId);

        $this->auditLogService->record('dashboard', 'dashboard_viewed', [], ['dashboard' => 'branch', 'filters' => $filters], $branchId, 'Branch dashboard viewed');

        return view('dashboard.branch', [
            'branch' => $branch,
            'summary' => $this->dashboardService->branch($user, $branchId, $filters),
            'employeePanel' => $this->buildEmployeePanel($user->id),
            'filters' => $filters,
            'branches' => $user->role?->code === config('rms.owner_role_code')
                ? Branch::query()->where('is_active', true)->orderBy('branch_name')->get()
                : $user->branches()->orderBy('branch_name')->get(),
        ]);
    }

    private function buildEmployeePanel(int $userId): ?array
    {
        $user = auth()->user();

        if (! $user || in_array($user->role?->code, [config('rms.owner_role_code'), 'super_admin', 'branch_manager'], true)) {
            return null;
        }

        $latestAttendance = AttendanceLog::query()
            ->with('branch')
            ->where('user_id', $userId)
            ->orderByDesc('attendance_date')
            ->orderByDesc('id')
            ->first();

        $latestPayslip = Payslip::query()
            ->with(['payrollItem.run.period'])
            ->whereHas('payrollItem', fn ($q) => $q->where('user_id', $userId))
            ->latest('generated_at')
            ->first();

        $canAccessChat = $user->hasPermission('access_chat');
        $roomIds = $canAccessChat
            ? ChatRoomMember::query()
                ->where('user_id', $userId)
                ->where('status', 'active')
                ->pluck('chat_room_id')
                ->all()
            : [];

        $unreadMessages = $canAccessChat
            ? ChatMessage::query()
                ->whereIn('chat_room_id', $roomIds)
                ->where('sender_id', '!=', $userId)
                ->whereNull('deleted_at')
                ->whereDoesntHave('reads', fn ($reads) => $reads->where('user_id', $userId))
                ->count()
            : 0;

        $recentMessages = $canAccessChat
            ? ChatMessage::query()
                ->with([
                    'sender',
                    'room',
                    'reads' => fn ($reads) => $reads->where('user_id', $userId),
                ])
                ->whereIn('chat_room_id', $roomIds)
                ->whereNull('deleted_at')
                ->latest('id')
                ->limit(4)
                ->get()
            : collect();

        $canViewNotifications = $user->hasPermission('view_notification_center');
        $unreadNotifications = $canViewNotifications
            ? $this->notificationService->communicationUnreadCount($user)
            : 0;
        $recentNotifications = $canViewNotifications
            ? $this->notificationService->recentCommunicationForUser($user, 4)
            : collect();

        $announcementQuery = Announcement::query()
            ->with([
                'creator',
                'reads' => fn ($reads) => $reads->where('user_id', $userId),
            ])
            ->latest('is_pinned')
            ->latest('published_at')
            ->latest('id');

        $this->announcementTargetService->scopeVisibleToUser($announcementQuery, $user);

        $announcementQuery->where(function ($active): void {
            $active->whereIn('status', ['published', 'scheduled'])
                ->where(function ($window): void {
                    $window->whereNull('publish_start_at')
                        ->orWhere('publish_start_at', '<=', now());
                })
                ->where(function ($window): void {
                    $window->whereNull('publish_end_at')
                        ->orWhere('publish_end_at', '>=', now());
                });
        });

        $unreadAnnouncements = (clone $announcementQuery)
            ->whereDoesntHave('reads', fn ($reads) => $reads->where('user_id', $userId)->whereIn('acknowledgment_status', ['read', 'acknowledged']))
            ->count();

        $recentAnnouncements = (clone $announcementQuery)
            ->limit(4)
            ->get();

        $cards = [
                ['label' => 'Attendance This Month', 'value' => AttendanceLog::query()->where('user_id', $userId)->whereBetween('attendance_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])->count(), 'url' => route('hr.attendance.index')],
                ['label' => 'Pending Leave Requests', 'value' => LeaveRequest::query()->where('user_id', $userId)->whereIn('status', ['pending_manager', 'pending_hr'])->count(), 'url' => route('hr.leaves.index')],
                ['label' => 'Pending Overtime Requests', 'value' => OvertimeRequest::query()->where('user_id', $userId)->whereIn('status', ['pending_manager', 'pending_hr'])->count(), 'url' => route('hr.overtime.index')],
                ['label' => 'Payslips Available', 'value' => Payslip::query()->whereHas('payrollItem', fn ($q) => $q->where('user_id', $userId))->count(), 'url' => route('hr.payslips.index')],
                ['label' => 'Unread Announcements', 'value' => $unreadAnnouncements, 'url' => route('announcements.index')],
            ];

        if ($canAccessChat) {
            $cards[] = ['label' => 'Unread Messages', 'value' => $unreadMessages, 'url' => route('chat.index')];
        }

        if ($canViewNotifications) {
            $cards[] = ['label' => 'Unread Notifications', 'value' => $unreadNotifications, 'url' => route('communication.notifications.index')];
        }

        return [
            'cards' => $cards,
            'profile' => [
                'name' => $user->display_name,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role?->name ?? $user->role?->code ?? 'Staff User',
                'branch' => $user->primaryBranch?->branch_name ?? $user->primaryBranch?->name ?? 'N/A',
                'status' => $user->status,
                'quick_links' => [
                    ['label' => 'Attendance', 'url' => route('hr.attendance.index')],
                    ['label' => 'Leaves', 'url' => route('hr.leaves.index')],
                    ['label' => 'Overtime', 'url' => route('hr.overtime.index')],
                    ['label' => 'Payslips', 'url' => route('hr.payslips.index')],
                ],
            ],
            'latest_attendance' => $latestAttendance,
            'latest_payslip' => $latestPayslip,
            'recent_announcements' => $recentAnnouncements,
            'can_access_chat' => $canAccessChat,
            'active_chat_rooms' => count($roomIds),
            'recent_messages' => $recentMessages,
            'can_view_notifications' => $canViewNotifications,
            'recent_notifications' => $recentNotifications,
            'unread_notifications' => $unreadNotifications,
        ];
    }
}
