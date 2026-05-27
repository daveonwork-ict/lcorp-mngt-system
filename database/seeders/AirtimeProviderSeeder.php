<?php

namespace Database\Seeders;

use App\Models\AirtimeProvider;
use Illuminate\Database\Seeder;

class AirtimeProviderSeeder extends Seeder
{
    public function run(): void
    {
        $providers = [
            ['provider_code' => 'SMART', 'provider_name' => 'Smart', 'default_commission_type' => 'percentage', 'default_commission_value' => 3.00, 'status' => 'active'],
            ['provider_code' => 'TNT', 'provider_name' => 'TNT', 'default_commission_type' => 'percentage', 'default_commission_value' => 2.50, 'status' => 'active'],
            ['provider_code' => 'GLOBE', 'provider_name' => 'Globe', 'default_commission_type' => 'percentage', 'default_commission_value' => 2.75, 'status' => 'active'],
            ['provider_code' => 'TM', 'provider_name' => 'TM', 'default_commission_type' => 'percentage', 'default_commission_value' => 2.50, 'status' => 'active'],
            ['provider_code' => 'DITO', 'provider_name' => 'DITO', 'default_commission_type' => 'percentage', 'default_commission_value' => 2.25, 'status' => 'active'],
            ['provider_code' => 'OTHER', 'provider_name' => 'Other Provider', 'default_commission_type' => 'none', 'default_commission_value' => 0, 'status' => 'active'],
        ];

        foreach ($providers as $provider) {
            AirtimeProvider::query()->updateOrCreate(
                ['provider_code' => $provider['provider_code']],
                [
                    'provider_name' => $provider['provider_name'],
                    'description' => $provider['provider_name'].' airtime provider',
                    'logo' => null,
                    'default_commission_type' => $provider['default_commission_type'],
                    'default_commission_value' => $provider['default_commission_value'],
                    'status' => $provider['status'],
                ]
            );
        }
    }
}
