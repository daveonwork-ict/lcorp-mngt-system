<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (config('rms.default_settings', []) as $setting) {
            SystemSetting::query()->updateOrCreate(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'] ?? null,
                    'group' => $setting['group'] ?? 'general',
                ]
            );
        }
    }
}
