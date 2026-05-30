<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\EmployeeProfile;
use App\Models\LeaveRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\BranchSeeder;
use Database\Seeders\PermissionsSeeder;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HrReportsFeatureTest extends TestCase
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

    public function test_hr_reports_index_requires_permission(): void
    {
        $branch = Branch::query()->where('code', 'MAIN')->firstOrFail();
        $role = Role::query()->where('code', 'staff_user')->firstOrFail();

        $user = User::factory()->create([
            'role_id' => $role->id,
            'primary_branch_id' => $branch->id,
            'status' => 'active',
            'is_active' => true,
        ]);

        $user->branches()->syncWithoutDetaching([$branch->id => ['is_primary' => true]]);

        $this->actingAs($user)
            ->get('/hr/reports')
            ->assertStatus(403);
    }

    public function test_hr_reports_index_and_csv_export_work_with_permissions(): void
    {
        $branch = Branch::query()->where('code', 'MAIN')->firstOrFail();
        $role = Role::query()->where('code', 'branch_manager')->firstOrFail();

        $this->grantRolePermissions($role, ['view_hr_reports', 'export_hr_reports']);

        $user = User::factory()->create([
            'role_id' => $role->id,
            'primary_branch_id' => $branch->id,
            'status' => 'active',
            'is_active' => true,
        ]);

        $user->branches()->syncWithoutDetaching([$branch->id => ['is_primary' => true]]);

        EmployeeProfile::query()->create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'employment_date' => now()->subYear()->toDateString(),
            'employment_status' => 'active',
            'salary_type' => 'monthly',
            'salary_rate' => 20000,
        ]);

        LeaveRequest::query()->create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'leave_type' => 'vacation',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
            'total_days' => 2,
            'reason' => 'Feature test leave',
            'status' => 'pending_manager',
        ]);

        $this->actingAs($user)
            ->get('/hr/reports')
            ->assertOk()
            ->assertSee('HR Reports');

        $this->actingAs($user)
            ->get('/hr/reports/export-csv')
            ->assertOk()
            ->assertSee('Section,Reference,Employee/Period,Branch,Date,Status,Amount,Notes', false);
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
