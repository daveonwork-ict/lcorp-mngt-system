<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Branch::query()->updateOrCreate(
            ['code' => env('RMS_DEFAULT_BRANCH_CODE', 'MAIN')],
            [
                'branch_code' => env('RMS_DEFAULT_BRANCH_CODE', 'MAIN'),
                'branch_name' => env('RMS_DEFAULT_BRANCH_NAME', 'Main Branch'),
                'name' => env('RMS_DEFAULT_BRANCH_NAME', 'Main Branch'),
                'address' => env('RMS_DEFAULT_BRANCH_ADDRESS', 'TBD'),
                'operational_status' => 'active',
                'status' => 'active',
                'is_active' => true,
            ]
        );
    }
}
