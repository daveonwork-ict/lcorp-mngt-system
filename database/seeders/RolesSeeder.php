<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (config('rms.default_roles', []) as $role) {
            Role::query()->updateOrCreate(
                ['code' => $role['code']],
                [
                    'name' => $role['name'],
                    'description' => $role['description'] ?? null,
                    'is_system' => true,
                ]
            );
        }
    }
}
