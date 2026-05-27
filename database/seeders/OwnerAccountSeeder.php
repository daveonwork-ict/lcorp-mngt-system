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
                'employee_code' => env('RMS_OWNER_EMPLOYEE_CODE', 'EMP-OWNER-001'),
                'first_name' => env('RMS_OWNER_FIRST_NAME', 'RC'),
                'last_name' => env('RMS_OWNER_LAST_NAME', 'Owner'),
                'full_name' => env('RMS_OWNER_NAME', 'RC Store Owner'),
                'username' => env('RMS_OWNER_USERNAME', 'owner'),
                'password' => Hash::make(env('RMS_OWNER_PASSWORD', 'ChangeMe123!')),
                'role_id' => $ownerRole->id,
                'status' => 'active',
                'is_active' => true,
            ]
        );
    }
}
