<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['code' => 'SALARY', 'name' => 'Salary'],
            ['code' => 'GAS', 'name' => 'Gas'],
            ['code' => 'ELECTRICITY', 'name' => 'Electricity'],
            ['code' => 'WATER', 'name' => 'Water'],
            ['code' => 'INTERNET', 'name' => 'Internet'],
            ['code' => 'RENT', 'name' => 'Rent'],
            ['code' => 'LOAD_WALLET_FUNDING', 'name' => 'Load Wallet Funding'],
            ['code' => 'OFFICE_SUPPLIES', 'name' => 'Office Supplies'],
            ['code' => 'REPAIRS_MAINTENANCE', 'name' => 'Repairs and Maintenance'],
            ['code' => 'DELIVERY', 'name' => 'Delivery'],
            ['code' => 'MARKETING', 'name' => 'Marketing'],
            ['code' => 'MISC', 'name' => 'Miscellaneous'],
        ];

        foreach ($defaults as $entry) {
            ExpenseCategory::query()->updateOrCreate(
                ['category_code' => $entry['code']],
                [
                    'category_name' => $entry['name'],
                    'description' => $entry['name'].' expenses',
                    'requires_approval' => true,
                    'receipt_required' => false,
                    'status' => 'active',
                ]
            );
        }
    }
}
