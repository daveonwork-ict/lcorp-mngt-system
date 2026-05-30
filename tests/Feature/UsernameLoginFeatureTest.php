<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\BranchSeeder;
use Database\Seeders\PermissionsSeeder;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsernameLoginFeatureTest extends TestCase
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

    public function test_user_can_log_in_with_username(): void
    {
        $branch = Branch::query()->where('code', 'MAIN')->firstOrFail();
        $role = Role::query()->where('code', 'staff_user')->firstOrFail();

        $permissionIds = Permission::query()
            ->whereIn('code', ['view_branch_dashboard'])
            ->pluck('id')
            ->all();

        $role->permissions()->syncWithoutDetaching($permissionIds);

        $user = User::factory()->create([
            'role_id' => $role->id,
            'primary_branch_id' => $branch->id,
            'status' => 'active',
            'is_active' => true,
            'username' => 'username.login',
            'name' => 'Different Name Value',
            'full_name' => 'Different Name Value',
            'email' => 'username.login@example.com',
            'password' => 'secret-pass-123',
        ]);

        $user->branches()->syncWithoutDetaching([$branch->id => ['is_primary' => true]]);

        $response = $this->post(route('login.store'), [
            'login' => 'username.login',
            'password' => 'secret-pass-123',
        ]);

        $response->assertRedirect(route('dashboard.branch'));
        $this->assertAuthenticatedAs($user);
    }
}