<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = collect(config('rms.permission_catalog', []))
            ->flatMap(function (array $codes, string $module): array {
                return collect($codes)
                    ->map(fn (string $code): array => [
                        'code' => $code,
                        'name' => str($code)->replace('_', ' ')->title()->toString(),
                        'module' => $module,
                        'description' => 'Permission for '.$module.' module action '.$code,
                    ])
                    ->toArray();
            })
            ->values();

        $compatibilityPermissions = collect(config('rms.modules', []))
            ->map(fn (array $module): array => [
                'code' => $module['permission'],
                'name' => $module['name'].' View (Legacy)',
                'module' => str($module['slug'])->replace('-', '_')->toString(),
                'description' => 'Compatibility permission for existing prototype routes.',
            ])
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

        $permissions = $permissions->merge($compatibilityPermissions)->unique('code')->values();

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
