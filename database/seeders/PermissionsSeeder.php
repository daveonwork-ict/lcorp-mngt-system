<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = collect(config('rms.modules', []))
            ->map(function (array $module): array {
                return [
                    'code' => $module['permission'],
                    'name' => $module['name'].' View',
                    'module' => Str::of($module['slug'])->replace('-', '_')->toString(),
                    'description' => 'View access to '.$module['name'].' module.',
                ];
            })
            ->push([
                'code' => 'dashboard.owner.view',
                'name' => 'Owner Dashboard View',
                'module' => 'dashboard',
                'description' => 'Access owner executive dashboard.',
            ])
            ->push([
                'code' => 'dashboard.branch.view',
                'name' => 'Branch Dashboard View',
                'module' => 'dashboard',
                'description' => 'Access branch operational dashboard.',
            ]);

        foreach ($permissions as $permission) {
            Permission::query()->updateOrCreate(
                ['code' => $permission['code']],
                [
                    'name' => $permission['name'],
                    'module' => $permission['module'],
                    'description' => $permission['description'],
                ]
            );
        }
    }
}
