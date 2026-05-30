<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\AnnouncementTarget;
use App\Models\AttendanceLog;
use App\Models\Branch;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\PayrollItem;
use App\Models\PayrollPeriod;
use App\Models\PayrollRun;
use App\Models\Payslip;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
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
            ->whereIn('code', ['view_branch_dashboard', 'view_attendance', 'view_leave_requests', 'view_overtime_requests', 'view_payslips'])
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
        ]);

        $user->branches()->syncWithoutDetaching([$branch->id => ['is_primary' => true]]);

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
        ]);

        AnnouncementTarget::query()->create([
            'announcement_id' => $announcement->id,
            'target_type' => 'all_users',
            'target_id' => null,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard.branch', ['branch_id' => $branch->id]))
            ->assertOk()
            ->assertSee('Employee Self-Service')
            ->assertSee('Latest Attendance')
            ->assertSee('Latest Payslip')
            ->assertSee('PS-DASH-001')
            ->assertSee('Attendance This Month')
            ->assertSee('Pending Leave Requests')
            ->assertSee('Pending Overtime Requests')
            ->assertSee('Payslips Available')
            ->assertSee('Unread Announcements')
            ->assertSee('Latest Announcements')
            ->assertSee('Payroll release reminder');
    }
}