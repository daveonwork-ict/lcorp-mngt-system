<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\EmployeeSchedule;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\BranchSeeder;
use Database\Seeders\PermissionsSeeder;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeScheduleBulkCreateFeatureTest extends TestCase
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

    public function test_bulk_schedule_create_generates_selected_weekdays_in_range(): void
    {
        $branch = Branch::query()->where('code', 'MAIN')->firstOrFail();
        $role = Role::query()->where('code', 'staff_user')->firstOrFail();

        $permissionIds = Permission::query()
            ->whereIn('code', ['view_schedules', 'manage_schedules'])
            ->pluck('id')
            ->all();

        $role->permissions()->syncWithoutDetaching($permissionIds);

        $user = User::factory()->create([
            'role_id' => $role->id,
            'primary_branch_id' => $branch->id,
            'status' => 'active',
            'is_active' => true,
            'username' => 'schedule.bulk',
            'full_name' => 'Schedule Bulk User',
            'name' => 'Schedule Bulk User',
        ]);

        $user->branches()->syncWithoutDetaching([$branch->id => ['is_primary' => true]]);

        $this->actingAs($user)
            ->post(route('hr.schedules.store'), [
                'user_id' => $user->id,
                'branch_id' => $branch->id,
                'bulk_mode' => 1,
                'date_from' => '2026-06-01',
                'date_to' => '2026-06-07',
                'weekdays' => [1, 2, 3, 4, 5],
                'schedule_type' => 'fixed',
                'time_in' => '08:00',
                'time_out' => '17:00',
                'break_start' => '12:00',
                'break_end' => '13:00',
                'is_rest_day' => 0,
            ])
            ->assertRedirect(route('hr.schedules.index'));

        $this->assertSame(5, EmployeeSchedule::query()->where('user_id', $user->id)->count());

        $this->assertDatabaseHas('employee_schedules', [
            'user_id' => $user->id,
            'schedule_date' => '2026-06-01 00:00:00',
            'time_in' => '08:00',
            'time_out' => '17:00',
            'is_rest_day' => 0,
        ]);

        $this->assertDatabaseMissing('employee_schedules', [
            'user_id' => $user->id,
            'schedule_date' => '2026-06-07 00:00:00',
        ]);
    }
}
