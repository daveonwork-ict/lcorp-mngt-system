<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OwnerAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ownerRole = Role::query()->where('code', config('rms.owner_role_code'))->first();

        if (! $ownerRole) {
            return;
        }

        $permissionIds = Permission::query()->pluck('id')->all();
        $ownerRole->permissions()->syncWithoutDetaching($permissionIds);

        User::query()->updateOrCreate(
            ['email' => env('RMS_OWNER_EMAIL', 'owner@rcstore.local')],
            [
                'name' => env('RMS_OWNER_NAME', 'RC Store Owner'),
                'password' => Hash::make(env('RMS_OWNER_PASSWORD', 'ChangeMe123!')),
                'role_id' => $ownerRole->id,
                'is_active' => true,
            ]
        );
    }
}
