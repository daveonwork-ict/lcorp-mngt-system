<?php

namespace Tests\Feature;

use App\Models\AttendanceLog;
use App\Models\Branch;
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
use Database\Seeders\BranchSeeder;
use Database\Seeders\PermissionsSeeder;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EmployeeSelfServiceFeatureTest extends TestCase
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

    public function test_self_service_user_cannot_access_other_employee_hr_records(): void
    {
        Storage::fake();

        [$branch, $staffUser, $otherUser] = $this->makeStaffUsers();

        $attendance = AttendanceLog::query()->create([
            'user_id' => $otherUser->id,
            'branch_id' => $branch->id,
            'attendance_date' => now()->toDateString(),
            'time_in' => now()->subHour(),
            'attendance_status' => 'present',
            'device_info_in' => ['raw' => 'Other Device'],
            'selfie_time_in_path' => 'hr/attendance-selfies/other-in.jpg',
        ]);

        $leave = LeaveRequest::query()->create([
            'user_id' => $otherUser->id,
            'branch_id' => $branch->id,
            'leave_type' => 'vacation',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
            'total_days' => 2,
            'reason' => 'Other employee leave',
            'status' => 'pending_manager',
        ]);

        $overtime = OvertimeRequest::query()->create([
            'user_id' => $otherUser->id,
            'branch_id' => $branch->id,
            'overtime_date' => now()->toDateString(),
            'hours' => 2,
            'reason' => 'Other employee overtime',
            'status' => 'pending_manager',
        ]);

        [$ownPayslip, $otherPayslip] = $this->createPayslips($branch, $staffUser, $otherUser);

        Storage::put($attendance->selfie_time_in_path, 'other-selfie');

        $this->actingAs($staffUser)
            ->get(route('hr.attendance.show', $attendance))
            ->assertForbidden();

        $this->actingAs($staffUser)
            ->get(route('hr.attendance.selfies.preview', ['attendance' => $attendance, 'captureType' => 'in']))
            ->assertForbidden();

        $this->actingAs($staffUser)
            ->post(route('hr.attendance.reverify', $attendance))
            ->assertForbidden();

        $this->actingAs($staffUser)
            ->get(route('hr.leaves.edit', $leave))
            ->assertForbidden();

        $this->actingAs($staffUser)
            ->get(route('hr.overtime.edit', $overtime))
            ->assertForbidden();

        $this->actingAs($staffUser)
            ->get(route('hr.payslips.index'))
            ->assertOk()
            ->assertSee($ownPayslip->payslip_number)
            ->assertDontSee($otherPayslip->payslip_number);

        $this->actingAs($staffUser)
            ->get(route('hr.payslips.download', $otherPayslip))
            ->assertForbidden();
    }

    public function test_self_service_writes_are_clamped_to_authenticated_employee(): void
    {
        Storage::fake();

        [$branch, $staffUser, $otherUser] = $this->makeStaffUsers();

        $this->actingAs($staffUser)
            ->post(route('hr.attendance.store'), [
                'user_id' => $otherUser->id,
                'branch_id' => $branch->id,
                'attendance_date' => now()->toDateString(),
                'time_in' => now()->format('Y-m-d H:i:s'),
                'selfie_time_in' => UploadedFile::fake()->image('time-in.jpg'),
                'device_info_in' => 'Staff Device',
                'attendance_status' => 'present',
            ])
            ->assertRedirect(route('hr.attendance.index'));

        $attendance = AttendanceLog::query()->latest('id')->firstOrFail();

        $this->assertSame($staffUser->id, $attendance->user_id);
        $this->assertSame($branch->id, $attendance->branch_id);

        $this->actingAs($staffUser)
            ->put(route('hr.attendance.update', $attendance), [
                'user_id' => $otherUser->id,
                'branch_id' => $branch->id,
                'attendance_date' => $attendance->attendance_date->toDateString(),
                'time_in' => $attendance->time_in->format('Y-m-d H:i:s'),
                'device_info_in' => 'Updated Staff Device',
                'attendance_status' => 'late',
            ])
            ->assertRedirect(route('hr.attendance.index'));

        $attendance->refresh();
        $this->assertSame($staffUser->id, $attendance->user_id);

        $this->actingAs($staffUser)
            ->post(route('hr.leaves.store'), [
                'user_id' => $otherUser->id,
                'branch_id' => $branch->id,
                'leave_type' => 'vacation',
                'start_date' => now()->toDateString(),
                'end_date' => now()->addDay()->toDateString(),
                'reason' => 'Need leave',
            ])
            ->assertRedirect(route('hr.leaves.index'));

        $leave = LeaveRequest::query()->latest('id')->firstOrFail();

        $this->assertSame($staffUser->id, $leave->user_id);
        $this->assertSame($branch->id, $leave->branch_id);

        $this->actingAs($staffUser)
            ->put(route('hr.leaves.update', $leave), [
                'user_id' => $otherUser->id,
                'branch_id' => $branch->id,
                'leave_type' => 'sick',
                'start_date' => $leave->start_date->toDateString(),
                'end_date' => $leave->end_date->toDateString(),
                'reason' => 'Updated leave',
                'status' => 'pending_manager',
            ])
            ->assertRedirect(route('hr.leaves.index'));

        $leave->refresh();
        $this->assertSame($staffUser->id, $leave->user_id);

        $this->actingAs($staffUser)
            ->post(route('hr.overtime.store'), [
                'user_id' => $otherUser->id,
                'branch_id' => $branch->id,
                'overtime_date' => now()->toDateString(),
                'hours' => 2,
                'reason' => 'Need overtime',
            ])
            ->assertRedirect(route('hr.overtime.index'));

        $overtime = OvertimeRequest::query()->latest('id')->firstOrFail();

        $this->assertSame($staffUser->id, $overtime->user_id);
        $this->assertSame($branch->id, $overtime->branch_id);

        $this->actingAs($staffUser)
            ->put(route('hr.overtime.update', $overtime), [
                'user_id' => $otherUser->id,
                'branch_id' => $branch->id,
                'overtime_date' => $overtime->overtime_date->toDateString(),
                'hours' => 3,
                'reason' => 'Updated overtime',
                'status' => 'pending_manager',
            ])
            ->assertRedirect(route('hr.overtime.index'));

        $overtime->refresh();
        $this->assertSame($staffUser->id, $overtime->user_id);
    }

    public function test_self_service_attendance_is_blocked_without_plotted_schedule(): void
    {
        Storage::fake();

        [$branch, $staffUser] = $this->makeStaffUsers();

        EmployeeSchedule::query()->where('user_id', $staffUser->id)->delete();

        $this->actingAs($staffUser)
            ->post(route('hr.attendance.store'), [
                'user_id' => $staffUser->id,
                'branch_id' => $branch->id,
                'attendance_date' => now('Asia/Manila')->toDateString(),
                'time_in' => now('Asia/Manila')->format('Y-m-d H:i:s'),
                'selfie_time_in' => UploadedFile::fake()->image('time-in.jpg'),
                'device_info_in' => 'Staff Device',
                'attendance_status' => 'present',
            ])
            ->assertSessionHasErrors('schedule_id');

        $this->assertDatabaseCount((new AttendanceLog())->getTable(), 0);
    }

    public function test_self_service_attendance_index_only_shows_own_attendance(): void
    {
        [$branch, $staffUser, $otherUser] = $this->makeStaffUsers();

        AttendanceLog::query()->create([
            'user_id' => $staffUser->id,
            'branch_id' => $branch->id,
            'attendance_date' => now('Asia/Manila')->toDateString(),
            'time_in' => now()->subHour(),
            'attendance_status' => 'present',
            'device_info_in' => ['raw' => 'Staff Device'],
        ]);

        AttendanceLog::query()->create([
            'user_id' => $otherUser->id,
            'branch_id' => $branch->id,
            'attendance_date' => now('Asia/Manila')->toDateString(),
            'time_in' => now()->subMinutes(30),
            'attendance_status' => 'present',
            'device_info_in' => ['raw' => 'Other Device'],
        ]);

        $this->actingAs($staffUser)
            ->get(route('hr.attendance.index'))
            ->assertOk()
            ->assertSee($staffUser->display_name)
            ->assertDontSee($otherUser->display_name);
    }

    public function test_self_service_user_cannot_edit_attendance_schedule(): void
    {
        [$branch, $staffUser, $otherUser] = $this->makeStaffUsers();

        $attendance = AttendanceLog::query()->create([
            'user_id' => $staffUser->id,
            'branch_id' => $branch->id,
            'attendance_date' => now('Asia/Manila')->toDateString(),
            'schedule_id' => EmployeeSchedule::query()->where('user_id', $staffUser->id)->value('id'),
            'time_in' => now()->subHour(),
            'attendance_status' => 'present',
            'device_info_in' => ['raw' => 'Staff Device'],
        ]);

        $otherSchedule = EmployeeSchedule::query()->create([
            'user_id' => $otherUser->id,
            'branch_id' => $branch->id,
            'schedule_date' => now('Asia/Manila')->toDateString(),
            'schedule_type' => 'fixed',
            'time_in' => '10:00',
            'time_out' => '19:00',
            'is_rest_day' => false,
        ]);

        $this->actingAs($staffUser)
            ->put(route('hr.attendance.update', $attendance), [
                'user_id' => $staffUser->id,
                'branch_id' => $branch->id,
                'attendance_date' => now('Asia/Manila')->toDateString(),
                'schedule_id' => $otherSchedule->id,
                'time_in' => now('Asia/Manila')->format('Y-m-d H:i:s'),
                'device_info_in' => 'Staff Device',
                'attendance_status' => 'present',
            ])
            ->assertSessionHasErrors('schedule_id');

        $attendance->refresh();
        $this->assertNotSame($otherSchedule->id, $attendance->schedule_id);
    }

    private function makeStaffUsers(): array
    {
        $branch = Branch::query()->where('code', 'MAIN')->firstOrFail();
        $role = Role::query()->where('code', 'staff_user')->firstOrFail();

        $this->grantRolePermissions($role, [
            'view_attendance',
            'view_leave_requests',
            'view_overtime_requests',
            'view_payslips',
        ]);

        $staffUser = User::factory()->create([
            'role_id' => $role->id,
            'primary_branch_id' => $branch->id,
            'status' => 'active',
            'is_active' => true,
            'full_name' => 'Staff Self Service',
            'name' => 'Staff Self Service',
            'employee_code' => 'EMP-SELF-001',
            'username' => 'selfstaff1',
        ]);

        $otherUser = User::factory()->create([
            'role_id' => $role->id,
            'primary_branch_id' => $branch->id,
            'status' => 'active',
            'is_active' => true,
            'full_name' => 'Other Employee',
            'name' => 'Other Employee',
            'employee_code' => 'EMP-SELF-002',
            'username' => 'selfstaff2',
        ]);

        $staffUser->branches()->syncWithoutDetaching([$branch->id => ['is_primary' => true]]);
        $otherUser->branches()->syncWithoutDetaching([$branch->id => ['is_primary' => true]]);

        EmployeeSchedule::query()->create([
            'user_id' => $staffUser->id,
            'branch_id' => $branch->id,
            'schedule_date' => Carbon::today('Asia/Manila')->toDateString(),
            'schedule_type' => 'fixed',
            'time_in' => '09:00',
            'time_out' => '18:00',
            'is_rest_day' => false,
        ]);

        return [$branch, $staffUser, $otherUser];
    }

    private function createPayslips(Branch $branch, User $staffUser, User $otherUser): array
    {
        $runOwner = User::factory()->create([
            'role_id' => Role::query()->where('code', 'super_admin')->value('id'),
            'primary_branch_id' => $branch->id,
            'status' => 'active',
            'is_active' => true,
        ]);

        $period = PayrollPeriod::query()->create([
            'period_code' => 'SELF-'.now()->format('YmdHis'),
            'payroll_period_type' => 'semi_monthly',
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'status' => 'open',
            'created_by' => $runOwner->id,
        ]);

        $run = PayrollRun::query()->create([
            'payroll_period_id' => $period->id,
            'branch_id' => $branch->id,
            'status' => 'released',
            'total_gross_pay' => 20000,
            'total_deductions' => 1000,
            'total_net_pay' => 19000,
            'generated_by' => $runOwner->id,
        ]);

        $ownItem = PayrollItem::query()->create([
            'payroll_run_id' => $run->id,
            'user_id' => $staffUser->id,
            'branch_id' => $branch->id,
            'basic_pay' => 10000,
            'gross_pay' => 10000,
            'total_deductions' => 500,
            'net_pay' => 9500,
            'status' => 'released',
        ]);

        $otherItem = PayrollItem::query()->create([
            'payroll_run_id' => $run->id,
            'user_id' => $otherUser->id,
            'branch_id' => $branch->id,
            'basic_pay' => 10000,
            'gross_pay' => 10000,
            'total_deductions' => 500,
            'net_pay' => 9500,
            'status' => 'released',
        ]);

        $ownPayslip = Payslip::query()->create([
            'payroll_item_id' => $ownItem->id,
            'payslip_number' => 'PS-SELF-001',
            'file_path' => 'hr/payslips/self-001.pdf',
            'generated_by' => $runOwner->id,
            'generated_at' => now(),
        ]);

        $otherPayslip = Payslip::query()->create([
            'payroll_item_id' => $otherItem->id,
            'payslip_number' => 'PS-SELF-002',
            'file_path' => 'hr/payslips/self-002.pdf',
            'generated_by' => $runOwner->id,
            'generated_at' => now(),
        ]);

        return [$ownPayslip, $otherPayslip];
    }

    private function grantRolePermissions(Role $role, array $codes): void
    {
        $permissionIds = Permission::query()
            ->whereIn('code', $codes)
            ->pluck('id')
            ->all();

        $role->permissions()->syncWithoutDetaching($permissionIds);
    }
}