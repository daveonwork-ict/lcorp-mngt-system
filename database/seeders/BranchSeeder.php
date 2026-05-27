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
                'name' => env('RMS_DEFAULT_BRANCH_NAME', 'Main Branch'),
                'address' => env('RMS_DEFAULT_BRANCH_ADDRESS', 'TBD'),
                'is_active' => true,
            ]
        );
    }
}
