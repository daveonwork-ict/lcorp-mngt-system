<?php

namespace Tests\Unit;

use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use App\Services\BranchAccessService;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BranchAccessServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            RolesSeeder::class,
            BranchSeeder::class,
        ]);
    }

    public function test_super_admin_has_global_branch_access(): void
    {
        $service = app(BranchAccessService::class);
        $role = Role::query()->where('code', 'super_admin')->firstOrFail();
        $branch = Branch::query()->where('code', 'MAIN')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $role->id,
            'primary_branch_id' => $branch->id,
            'status' => 'active',
            'is_active' => true,
        ]);

        $this->assertTrue($service->hasGlobalBranchAccess($admin));

        $allActiveBranchIds = Branch::query()->where('is_active', true)->pluck('id')->sort()->values()->all();
        $accessibleBranchIds = $service->accessibleBranches($admin)->pluck('id')->sort()->values()->all();

        $this->assertSame($allActiveBranchIds, $accessibleBranchIds);
    }

    public function test_branch_manager_is_limited_to_assigned_branches(): void
    {
        $service = app(BranchAccessService::class);
        $role = Role::query()->where('code', 'branch_manager')->firstOrFail();
        $main = Branch::query()->where('code', 'MAIN')->firstOrFail();
        $other = Branch::query()->where('code', 'RCS-LPZ')->firstOrFail();

        $manager = User::factory()->create([
            'role_id' => $role->id,
            'primary_branch_id' => $main->id,
            'status' => 'active',
            'is_active' => true,
        ]);

        $manager->branches()->syncWithoutDetaching([$main->id => ['is_primary' => true]]);

        $this->assertFalse($service->hasGlobalBranchAccess($manager));
        $this->assertTrue($service->canAccessBranch($manager, $main->id));
        $this->assertFalse($service->canAccessBranch($manager, $other->id));
    }
}
