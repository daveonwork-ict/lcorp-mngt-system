<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\AnnouncementRead;
use App\Models\AnnouncementTarget;
use App\Models\AttendanceLog;
use App\Models\Branch;
use App\Models\ChatMessage;
use App\Models\ChatMessageRead;
use App\Models\ChatRoom;
use App\Models\ChatRoomMember;
use App\Models\CommunicationNotification;
use App\Models\EmployeeSchedule;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\PayrollItem;
use App\Models\PayrollPeriod;
use App\Models\PayrollRun;
use App\Models\Payslip;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\ChatMessageService;
use Database\Seeders\BranchSeeder;
use Database\Seeders\PermissionsSeeder;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeDashboardFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            RolesSeeder::class,
            PermissionsSeeder::class,
            BranchSeeder::class,
        ]);
    }

    public function test_staff_user_sees_employee_self_service_panel_on_branch_dashboard(): void
    {
        $branch = Branch::query()->where('code', 'MAIN')->firstOrFail();
        $role = Role::query()->where('code', 'staff_user')->firstOrFail();

        $permissionIds = Permission::query()
            ->whereIn('code', ['view_branch_dashboard', 'view_attendance', 'view_leave_requests', 'view_overtime_requests', 'view_payslips', 'view_announcements', 'access_chat', 'view_notification_center', 'view_schedules'])
            ->pluck('id')
            ->all();

        $role->permissions()->syncWithoutDetaching($permissionIds);

        $user = User::factory()->create([
            'role_id' => $role->id,
            'primary_branch_id' => $branch->id,
            'status' => 'active',
            'is_active' => true,
            'username' => 'dashboard.staff',
            'full_name' => 'Dashboard Staff',
            'name' => 'Dashboard Staff',
            'email' => 'dashboard.staff@example.test',
        ]);

        $user->branches()->syncWithoutDetaching([$branch->id => ['is_primary' => true]]);

        $teammate = User::factory()->create([
            'role_id' => $role->id,
            'primary_branch_id' => $branch->id,
            'status' => 'active',
            'is_active' => true,
            'username' => 'dashboard.mate',
            'full_name' => 'Dashboard Teammate',
            'name' => 'Dashboard Teammate',
        ]);

        $teammate->branches()->syncWithoutDetaching([$branch->id => ['is_primary' => true]]);

        EmployeeSchedule::query()->create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'schedule_date' => now()->toDateString(),
            'schedule_type' => 'fixed',
            'time_in' => '09:00',
            'time_out' => '18:00',
            'is_rest_day' => false,
        ]);

        EmployeeSchedule::query()->create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'schedule_date' => now()->addDay()->toDateString(),
            'schedule_type' => 'fixed',
            'time_in' => '10:00',
            'time_out' => '19:00',
            'is_rest_day' => false,
        ]);

        AttendanceLog::query()->create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'attendance_date' => now()->toDateString(),
            'time_in' => now()->subHour(),
            'time_out' => now(),
            'device_info_in' => ['raw' => 'Dashboard Browser'],
            'attendance_status' => 'present',
        ]);

        LeaveRequest::query()->create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'leave_type' => 'vacation',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
            'total_days' => 2,
            'reason' => 'Family trip',
            'status' => 'pending_manager',
        ]);

        OvertimeRequest::query()->create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'overtime_date' => now()->toDateString(),
            'hours' => 2,
            'reason' => 'Month end',
            'status' => 'pending_hr',
        ]);

        $period = PayrollPeriod::query()->create([
            'period_code' => 'DASH-'.now()->format('YmdHis'),
            'payroll_period_type' => 'semi_monthly',
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'status' => 'open',
            'created_by' => $user->id,
        ]);

        $run = PayrollRun::query()->create([
            'payroll_period_id' => $period->id,
            'branch_id' => $branch->id,
            'status' => 'released',
            'generated_by' => $user->id,
        ]);

        $item = PayrollItem::query()->create([
            'payroll_run_id' => $run->id,
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'basic_pay' => 10000,
            'gross_pay' => 10000,
            'total_deductions' => 2500,
            'net_pay' => 7500,
            'status' => 'released',
        ]);

        Payslip::query()->create([
            'payroll_item_id' => $item->id,
            'payslip_number' => 'PS-DASH-001',
            'file_path' => 'hr/payslips/PS-DASH-001.txt',
            'generated_by' => $user->id,
            'generated_at' => now(),
        ]);

        $announcement = Announcement::query()->create([
            'announcement_number' => 'ANN-DASH-001',
            'title' => 'Payroll release reminder',
            'content' => 'Please review your latest payslip and attendance records.',
            'announcement_type' => 'system_notice',
            'priority_level' => 'important',
            'target_scope' => 'all_users',
            'status' => 'published',
            'created_by' => $user->id,
            'approved_by' => $user->id,
            'published_at' => now(),
            'is_pinned' => true,
            'requires_acknowledgment' => true,
        ]);

        AnnouncementTarget::query()->create([
            'announcement_id' => $announcement->id,
            'target_type' => 'all_users',
            'target_id' => null,
        ]);

        $room = ChatRoom::query()->create([
            'room_number' => 'ROOM-DASH-001',
            'room_name' => 'Team Pulse',
            'room_type' => 'group',
            'branch_id' => $branch->id,
            'created_by' => $teammate->id,
            'status' => 'active',
        ]);

        ChatRoomMember::query()->create([
            'chat_room_id' => $room->id,
            'user_id' => $user->id,
            'role_in_room' => 'member',
            'joined_at' => now()->subDay(),
            'status' => 'active',
        ]);

        ChatRoomMember::query()->create([
            'chat_room_id' => $room->id,
            'user_id' => $teammate->id,
            'role_in_room' => 'moderator',
            'joined_at' => now()->subDay(),
            'status' => 'active',
        ]);

        $this->actingAs($teammate);

        $message = app(ChatMessageService::class)->send($room, $teammate, [
            'message_body' => 'Please confirm your shift handoff before lunch.',
            'message_type' => 'text',
        ]);

        $notification = CommunicationNotification::query()
            ->where('user_id', $user->id)
            ->where('chat_room_id', $room->id)
            ->latest('id')
            ->firstOrFail();

        $this->actingAs($user)
            ->get(route('dashboard.branch', ['branch_id' => $branch->id]))
            ->assertOk()
            ->assertSee('Employee Self-Service')
            ->assertSee('Employee Profile')
            ->assertSee('Dashboard Staff')
            ->assertSee('@dashboard.staff')
            ->assertSee('dashboard.staff@example.test')
            ->assertSee('Role')
            ->assertSee('Branch')
            ->assertSee('Status')
            ->assertSee('Today Shift')
            ->assertSee('Next Shift')
            ->assertSee('09:00 - 18:00')
            ->assertSee('10:00 - 19:00')
            ->assertSee('Attendance')
            ->assertSee('Leaves')
            ->assertSee('Overtime')
            ->assertSee('Schedules')
            ->assertSee('Latest Attendance')
            ->assertSee('Latest Payslip')
            ->assertSee('PS-DASH-001')
            ->assertSee('Attendance This Month')
            ->assertSee('Pending Leave Requests')
            ->assertSee('Pending Overtime Requests')
            ->assertSee('Payslips Available')
            ->assertSee('Unread Announcements')
            ->assertSee('Unread Messages')
            ->assertSee('Unread Notifications')
            ->assertSee('Latest Announcements')
            ->assertSee('Payroll release reminder')
            ->assertSee('Recent Chat Activity')
            ->assertSee('Team Pulse')
            ->assertSee('Please confirm your shift handoff before lunch.')
            ->assertSee('Recent Notifications')
            ->assertSee('New chat message')
            ->assertSee('Dashboard Teammate: Please confirm your shift handoff before lunch.')
            ->assertSee('Open Notification Center')
            ->assertSee('/chat/rooms/'.$room->id, false)
            ->assertSee('Mark Room Read')
            ->assertSee('Mark Read')
            ->assertSee('Acknowledge');

        $this->actingAs($user)
            ->post(route('announcements.read.mark', $announcement), [
                '_token' => csrf_token(),
            ])
            ->assertRedirect();

        $this->actingAs($user)
            ->post(route('chat.rooms.read.mark', $room), [
                '_token' => csrf_token(),
            ])
            ->assertRedirect();

        $this->actingAs($user)
            ->post(route('communication.notifications.read', $notification), [
                '_token' => csrf_token(),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('announcement_reads', [
            'announcement_id' => $announcement->id,
            'user_id' => $user->id,
            'acknowledgment_status' => 'read',
        ]);

        $this->assertDatabaseHas((new ChatMessageRead())->getTable(), [
            'chat_message_id' => $message->id,
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('communication_notifications', [
            'id' => $notification->id,
            'user_id' => $user->id,
            'is_read' => true,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard.branch', ['branch_id' => $branch->id]))
            ->assertOk()
            ->assertSee('Read')
            ->assertSee('Seen')
            ->assertSee('Acknowledge');
    }
}