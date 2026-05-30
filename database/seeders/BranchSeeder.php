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
        $branches = [
            [
                'code' => 'RCS-LPZ',
                'name' => 'RC Station - La Paz Branch',
                'address' => 'RCS Z Town Plaza Bldg., San Isidro (Pob.), La Paz, Tarlac',
            ],
            [
                'code' => 'RCS-ZRG',
                'name' => 'RC Station - Zaragoza Branch',
                'address' => 'Purok Biak na Bato, Brgy. Del Pilar East, Zaragoza, Nueva Ecija',
            ],
            [
                'code' => 'GB-CPS',
                'name' => 'Gadgets & Beauty - Capas Branch',
                'address' => 'CVO Building, Brgy. Cubcub, Capas, Tarlac',
            ],
            [
                'code' => 'KRS-TARLAC',
                'name' => 'Kriscielo - Tarlac Branch / Magic Star Mall',
                'address' => 'Upper Ground Floor, Magic Star Mall, Cut-Cut 1, Tarlac City',
            ],
            [
                'code' => 'MAIN',
                'name' => 'Head Office - Main Warehouse',
                'address' => 'San Isidro, La Paz Tarlac',
            ],
        ];

        foreach ($branches as $branch) {
            Branch::query()->updateOrCreate(
                ['code' => $branch['code']],
                [
                    'branch_code' => $branch['code'],
                    'branch_name' => $branch['name'],
                    'name' => $branch['name'],
                    'address' => $branch['address'],
                    'operational_status' => 'active',
                    'status' => 'active',
                    'is_active' => true,
                ]
            );
        }
    }
}
