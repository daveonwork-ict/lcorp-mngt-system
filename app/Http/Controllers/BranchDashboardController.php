<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AttendanceLog;
use App\Models\Branch;
use App\Models\ChatMessage;
use App\Models\ChatRoomMember;
use App\Models\EmployeeSchedule;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\Payslip;
use App\Services\AnnouncementTargetService;
use App\Services\AuditLogService;
use App\Services\DashboardAnalyticsService;
use App\Services\NotificationService;
use App\Services\ReportFilterService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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

        $todayAttendance = AttendanceLog::query()
            ->where('user_id', $userId)
            ->whereDate('attendance_date', now()->toDateString())
            ->latest('id')
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

        $todaySchedule = EmployeeSchedule::query()
            ->where('user_id', $userId)
            ->whereDate('schedule_date', now()->toDateString())
            ->latest('id')
            ->first();

        $nextSchedule = EmployeeSchedule::query()
            ->where('user_id', $userId)
            ->whereDate('schedule_date', '>', now()->toDateString())
            ->orderBy('schedule_date')
            ->orderBy('time_in')
            ->orderBy('id')
            ->first();

        $canViewSchedules = $user->hasPermission('view_schedules');

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

        $quickLinks = [
            ['label' => 'Attendance', 'url' => route('hr.attendance.index')],
            ['label' => 'Leaves', 'url' => route('hr.leaves.index')],
            ['label' => 'Overtime', 'url' => route('hr.overtime.index')],
            ['label' => 'Payslips', 'url' => route('hr.payslips.index')],
        ];

        if ($canViewSchedules) {
            $quickLinks[] = ['label' => 'Schedules', 'url' => route('hr.schedules.index')];
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
                'today_schedule' => $this->formatScheduleSummary($todaySchedule),
                'next_schedule' => $this->formatScheduleSummary($nextSchedule),
                'today_shift_status' => $this->buildTodayShiftStatus($todaySchedule, $todayAttendance),
                'today_attendance_status' => $this->buildTodayAttendanceStatus($todayAttendance),
                'last_sync' => $this->resolveLastSyncMeta($latestAttendance, $todaySchedule, $nextSchedule),
                'quick_links' => $quickLinks,
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

    private function formatScheduleSummary(?EmployeeSchedule $schedule): ?array
    {
        if (! $schedule) {
            return null;
        }

        $timeIn = $schedule->time_in ? substr((string) $schedule->time_in, 0, 5) : null;
        $timeOut = $schedule->time_out ? substr((string) $schedule->time_out, 0, 5) : null;

        if ($schedule->is_rest_day) {
            $window = 'Rest Day';
        } elseif ($timeIn && $timeOut) {
            $window = $timeIn.' - '.$timeOut;
        } elseif ($timeIn) {
            $window = $timeIn;
        } else {
            $window = 'TBD';
        }

        return [
            'date' => optional($schedule->schedule_date)->format('Y-m-d'),
            'window' => $window,
        ];
    }

    private function buildTodayShiftStatus(?EmployeeSchedule $todaySchedule, ?AttendanceLog $todayAttendance): ?array
    {
        if (! $todaySchedule) {
            return null;
        }

        if ($todaySchedule->is_rest_day) {
            return ['label' => 'Rest Day', 'tone' => 'secondary'];
        }

        if (! $todaySchedule->time_in) {
            return null;
        }

        if (! $todayAttendance?->time_in) {
            return ['label' => 'No Time In Yet', 'tone' => 'warning'];
        }

        $scheduledAt = Carbon::parse($todaySchedule->schedule_date->format('Y-m-d').' '.$todaySchedule->time_in);
        $actualTimeIn = $todayAttendance->time_in instanceof Carbon
            ? $todayAttendance->time_in
            : Carbon::parse($todayAttendance->time_in);

        if ($actualTimeIn->greaterThan($scheduledAt)) {
            $lateMinutes = $scheduledAt->diffInMinutes($actualTimeIn);

            return ['label' => 'Late by '.$lateMinutes.'m', 'tone' => 'danger'];
        }

        return ['label' => 'On Time', 'tone' => 'success'];
    }

    private function buildTodayAttendanceStatus(?AttendanceLog $todayAttendance): ?array
    {
        if (! $todayAttendance?->time_in) {
            return null;
        }

        if (! $todayAttendance->time_out) {
            return ['label' => 'Time Out Missing', 'tone' => 'warning'];
        }

        return ['label' => 'Attendance Complete', 'tone' => 'primary'];
    }

    private function resolveLastSyncMeta(?AttendanceLog $latestAttendance, ?EmployeeSchedule $todaySchedule, ?EmployeeSchedule $nextSchedule): ?array
    {
        $candidates = [
            ['source' => 'Attendance', 'timestamp' => $latestAttendance?->updated_at, 'rank' => 0],
            ['source' => 'Today Schedule', 'timestamp' => $todaySchedule?->updated_at, 'rank' => 1],
            ['source' => 'Next Schedule', 'timestamp' => $nextSchedule?->updated_at, 'rank' => 2],
        ];

        $candidates = array_values(array_filter($candidates, fn (array $candidate) => $candidate['timestamp'] !== null));

        if ($candidates === []) {
            return null;
        }

        usort($candidates, function (array $left, array $right): int {
            $leftTimestamp = $left['timestamp'] instanceof Carbon ? $left['timestamp'] : Carbon::parse($left['timestamp']);
            $rightTimestamp = $right['timestamp'] instanceof Carbon ? $right['timestamp'] : Carbon::parse($right['timestamp']);

            if ($leftTimestamp->equalTo($rightTimestamp)) {
                return $left['rank'] <=> $right['rank'];
            }

            return $leftTimestamp->greaterThan($rightTimestamp) ? -1 : 1;
        });

        $latest = $candidates[0];
        $latestTimestamp = $latest['timestamp'] instanceof Carbon ? $latest['timestamp'] : Carbon::parse($latest['timestamp']);

        return [
            'at' => $latestTimestamp->format('Y-m-d H:i'),
            'source' => $latest['source'],
        ];
    }
}
