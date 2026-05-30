<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            PermissionsSeeder::class,
            DefaultRolePermissionsSeeder::class,
            BranchSeeder::class,
            SystemSettingsSeeder::class,
            PaymentMethodSeeder::class,
            AirtimeProviderSeeder::class,
            ExpenseCategorySeeder::class,
            OwnerAccountSeeder::class,
            DefaultRoleUsersSeeder::class,
        ]);

        $owner = User::query()->where('email', env('RMS_OWNER_EMAIL', 'owner@rcstore.local'))->first();
        $branch = Branch::query()->where('code', env('RMS_DEFAULT_BRANCH_CODE', 'MAIN'))->first();

        if ($owner && $branch) {
            $owner->branches()->syncWithoutDetaching([
                $branch->id => ['is_primary' => true],
            ]);
        }
    }
}
