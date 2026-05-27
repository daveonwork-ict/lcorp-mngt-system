<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            ['payment_method_name' => 'Cash', 'payment_type' => 'Cash', 'requires_reference' => false, 'status' => 'active'],
            ['payment_method_name' => 'GCash', 'payment_type' => 'E-Wallet', 'requires_reference' => true, 'status' => 'active'],
            ['payment_method_name' => 'Maya', 'payment_type' => 'E-Wallet', 'requires_reference' => true, 'status' => 'active'],
            ['payment_method_name' => 'Bank Transfer', 'payment_type' => 'Bank', 'requires_reference' => true, 'status' => 'active'],
            ['payment_method_name' => 'Card', 'payment_type' => 'Card', 'requires_reference' => true, 'status' => 'active'],
            ['payment_method_name' => 'Split Payment', 'payment_type' => 'Other', 'requires_reference' => false, 'status' => 'active'],
        ];

        foreach ($methods as $method) {
            PaymentMethod::query()->updateOrCreate(
                ['payment_method_name' => $method['payment_method_name']],
                $method
            );
        }
    }
}
