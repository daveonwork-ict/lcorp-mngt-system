<?php

namespace Database\Seeders;

use App\Models\AirtimeProvider;
use App\Models\Branch;
use App\Models\Brand;
use App\Models\EmployeeProfile;
use App\Models\ExpenseCategory;
use App\Models\PagibigContributionTable;
use App\Models\PaymentMethod;
use App\Models\ProductImei;
use App\Models\PhilhealthContributionTable;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\PayrollPeriod;
use App\Models\Role;
use App\Models\SssContributionTable;
use App\Models\User;
use App\Models\WithholdingTaxTable;
use App\Services\AirtimeFundingService;
use App\Services\AirtimeTransactionService;
use App\Services\AirtimeWalletAdjustmentService;
use App\Services\AirtimeWalletService;
use App\Services\AttendanceLogService;
use App\Services\CashAdvanceService;
use App\Services\EmployeeScheduleService;
use App\Services\ExpenseApprovalService;
use App\Services\ExpenseService;
use App\Services\FundTransferService;
use App\Services\InventoryAdjustmentService;
use App\Services\InventoryTransferService;
use App\Services\LeaveRequestService;
use App\Services\LoanService;
use App\Services\OvertimeRequestService;
use App\Services\PayrollService;
use App\Services\SalesService;
use App\Services\StockInService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class FullDemoOperationsSeeder extends Seeder
{
    private const DEMO_PASSWORD = 'Demo@123456';

    private User $actor;

    private array $numberCounters = [];

    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            PermissionsSeeder::class,
            DefaultRolePermissionsSeeder::class,
            BranchSeeder::class,
            SystemSettingsSeeder::class,
            PaymentMethodSeeder::class,
            AirtimeProviderSeeder::class,
            ExpenseCategorySeeder::class,
            OwnerAccountSeeder::class,
            DefaultRoleUsersSeeder::class,
        ]);

        $this->actor = $this->resolveActor();
        Auth::login($this->actor);

        $catalog = $this->seedCatalog();
        $this->seedStatutoryTables();

        $branches = Branch::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        if ($branches->isEmpty()) {
            $this->command?->warn('No active branches found. Full demo seeder skipped.');

            return;
        }

        $branchSeeds = [];

        foreach ($branches as $index => $branch) {
            $users = $this->seedBranchUsers($branch, $index + 1);
            $inventoryProducts = $this->seedInventoryFlow($branch, $catalog['products']);

            $this->seedPosAndSalesFlow($branch, $users['cashier'], $catalog['payment_methods'], $catalog['products']);
            $this->seedAirtimeFlow($branch, $users['cashier'], $catalog['providers'], $catalog['payment_methods']);
            $this->seedHrFlow($branch, $users, $catalog['payroll_service']);
            $this->seedFinanceFlow($branch, $users['manager'], $catalog['payment_methods'], $catalog['expense_category']);

            $branchSeeds[] = [
                'branch' => $branch,
                'transfer_product' => $inventoryProducts['transfer_product'],
            ];
        }

        $this->seedInventoryTransfers($branchSeeds);
        $this->seedFundTransfers($branches, $catalog['payment_methods']);

        Auth::logout();

        $this->command?->info('Full demo operational transactions seeded across all active branches.');
    }

    private function resolveActor(): User
    {
        $actor = User::query()
            ->where('email', env('RMS_SUPER_ADMIN_EMAIL', 'superadmin@rcstore.local'))
            ->first();

        if (! $actor) {
            $actor = User::query()
                ->where('email', env('RMS_OWNER_EMAIL', 'owner@rcstore.local'))
                ->first();
        }

        if (! $actor) {
            $actor = User::query()->first();
        }

        if (! $actor) {
            throw new \RuntimeException('No user available for FullDemoOperationsSeeder actor context.');
        }

        return $actor;
    }

    private function seedCatalog(): array
    {
        $category = ProductCategory::query()->updateOrCreate(
            ['category_code' => 'DEMO-MOBILE'],
            ['category_name' => 'Demo Mobile Devices', 'description' => 'Demo operational mobile devices', 'status' => 'active']
        );

        $accessoryCategory = ProductCategory::query()->updateOrCreate(
            ['category_code' => 'DEMO-ACCESSORY'],
            ['category_name' => 'Demo Accessories', 'description' => 'Demo operational accessories', 'status' => 'active']
        );

        $brand = Brand::query()->updateOrCreate(
            ['brand_code' => 'DEMO-BRAND'],
            ['brand_name' => 'DemoTech', 'description' => 'DemoTech products', 'status' => 'active']
        );

        $products = [
            Product::query()->updateOrCreate(
                ['sku' => 'DEMO-PHONE-A'],
                [
                    'product_code' => 'DEMO-PROD-PHONE-A',
                    'barcode' => '8999000000011',
                    'product_name' => 'DemoPhone Alpha 128GB',
                    'category_id' => $category->id,
                    'brand_id' => $brand->id,
                    'model' => 'Alpha',
                    'variant' => '128GB',
                    'color' => 'Black',
                    'description' => 'Serialized demo smartphone',
                    'cost_price' => 7800,
                    'selling_price' => 9950,
                    'wholesale_price' => 9450,
                    'reorder_level' => 3,
                    'warranty_duration' => 12,
                    'warranty_duration_type' => 'month',
                    'is_serialized' => true,
                    'is_imei_required' => true,
                    'status' => 'active',
                    'created_by' => $this->actor->id,
                    'updated_by' => $this->actor->id,
                ]
            ),
            Product::query()->updateOrCreate(
                ['sku' => 'DEMO-PHONE-B'],
                [
                    'product_code' => 'DEMO-PROD-PHONE-B',
                    'barcode' => '8999000000012',
                    'product_name' => 'DemoPhone Nova 64GB',
                    'category_id' => $category->id,
                    'brand_id' => $brand->id,
                    'model' => 'Nova',
                    'variant' => '64GB',
                    'color' => 'Blue',
                    'description' => 'Secondary serialized demo smartphone',
                    'cost_price' => 6200,
                    'selling_price' => 8350,
                    'wholesale_price' => 7950,
                    'reorder_level' => 4,
                    'warranty_duration' => 12,
                    'warranty_duration_type' => 'month',
                    'is_serialized' => true,
                    'is_imei_required' => true,
                    'status' => 'active',
                    'created_by' => $this->actor->id,
                    'updated_by' => $this->actor->id,
                ]
            ),
            Product::query()->updateOrCreate(
                ['sku' => 'DEMO-CHARGER-20W'],
                [
                    'product_code' => 'DEMO-PROD-CHARGER',
                    'barcode' => '8999000000013',
                    'product_name' => 'Demo 20W Fast Charger',
                    'category_id' => $accessoryCategory->id,
                    'brand_id' => $brand->id,
                    'model' => 'Charge20',
                    'variant' => 'USB-C',
                    'color' => 'White',
                    'description' => 'Fast charger accessory',
                    'cost_price' => 220,
                    'selling_price' => 390,
                    'wholesale_price' => 350,
                    'reorder_level' => 15,
                    'warranty_duration' => 3,
                    'warranty_duration_type' => 'month',
                    'is_serialized' => false,
                    'is_imei_required' => false,
                    'status' => 'active',
                    'created_by' => $this->actor->id,
                    'updated_by' => $this->actor->id,
                ]
            ),
        ];

        $paymentMethods = PaymentMethod::query()->where('status', 'active')->get()->keyBy(function (PaymentMethod $method): string {
            return Str::lower($method->payment_method_name);
        });

        $providers = AirtimeProvider::query()->where('status', 'active')->orderBy('id')->get();
        $expenseCategory = ExpenseCategory::query()->where('category_code', 'MISC')->first()
            ?? ExpenseCategory::query()->firstOrFail();

        return [
            'products' => $products,
            'payment_methods' => $paymentMethods,
            'providers' => $providers,
            'expense_category' => $expenseCategory,
            'payroll_service' => app(PayrollService::class),
        ];
    }

    private function seedBranchUsers(Branch $branch, int $sequence): array
    {
        $cashierRole = Role::query()->where('code', 'cashier')->first()
            ?? Role::query()->where('code', 'staff_user')->firstOrFail();
        $staffRole = Role::query()->where('code', 'staff_user')->firstOrFail();
        $managerRole = Role::query()->where('code', 'branch_manager')->first()
            ?? Role::query()->where('code', 'super_admin')->firstOrFail();

        $branchToken = Str::of((string) ($branch->code ?? $branch->branch_code ?? 'branch-'.$branch->id))
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '')
            ->toString();

        $cashier = User::query()->updateOrCreate(
            ['email' => 'demo.cashier.'.$branchToken.'@rcstore.local'],
            [
                'employee_code' => sprintf('EMP-DEMO-%s-CASH', Str::upper($branchToken)),
                'first_name' => 'Demo',
                'last_name' => 'Cashier '.$sequence,
                'full_name' => 'Demo Cashier '.$sequence,
                'name' => 'Demo Cashier '.$sequence,
                'username' => 'demo_cashier_'.$branchToken,
                'mobile_number' => '0917'.str_pad((string) (3000000 + $sequence), 7, '0', STR_PAD_LEFT),
                'role_id' => $cashierRole->id,
                'primary_branch_id' => $branch->id,
                'status' => 'active',
                'is_active' => true,
                'password' => Hash::make(self::DEMO_PASSWORD),
            ]
        );

        $staff = User::query()->updateOrCreate(
            ['email' => 'demo.staff.'.$branchToken.'@rcstore.local'],
            [
                'employee_code' => sprintf('EMP-DEMO-%s-STAFF', Str::upper($branchToken)),
                'first_name' => 'Demo',
                'last_name' => 'Staff '.$sequence,
                'full_name' => 'Demo Staff '.$sequence,
                'name' => 'Demo Staff '.$sequence,
                'username' => 'demo_staff_'.$branchToken,
                'mobile_number' => '0917'.str_pad((string) (4000000 + $sequence), 7, '0', STR_PAD_LEFT),
                'role_id' => $staffRole->id,
                'primary_branch_id' => $branch->id,
                'status' => 'active',
                'is_active' => true,
                'password' => Hash::make(self::DEMO_PASSWORD),
            ]
        );

        $manager = User::query()->updateOrCreate(
            ['email' => 'demo.manager.'.$branchToken.'@rcstore.local'],
            [
                'employee_code' => sprintf('EMP-DEMO-%s-MGR', Str::upper($branchToken)),
                'first_name' => 'Demo',
                'last_name' => 'Manager '.$sequence,
                'full_name' => 'Demo Manager '.$sequence,
                'name' => 'Demo Manager '.$sequence,
                'username' => 'demo_manager_'.$branchToken,
                'mobile_number' => '0917'.str_pad((string) (5000000 + $sequence), 7, '0', STR_PAD_LEFT),
                'role_id' => $managerRole->id,
                'primary_branch_id' => $branch->id,
                'status' => 'active',
                'is_active' => true,
                'password' => Hash::make(self::DEMO_PASSWORD),
            ]
        );

        foreach ([$cashier, $staff, $manager] as $user) {
            $user->branches()->syncWithoutDetaching([
                $branch->id => ['is_primary' => true],
            ]);
        }

        EmployeeProfile::query()->updateOrCreate(
            ['user_id' => $cashier->id],
            [
                'branch_id' => $branch->id,
                'employment_date' => now()->subMonths(8)->toDateString(),
                'employment_type' => 'regular',
                'employment_status' => 'active',
                'salary_type' => 'monthly',
                'salary_rate' => 16800,
            ]
        );

        EmployeeProfile::query()->updateOrCreate(
            ['user_id' => $staff->id],
            [
                'branch_id' => $branch->id,
                'employment_date' => now()->subMonths(6)->toDateString(),
                'employment_type' => 'regular',
                'employment_status' => 'active',
                'salary_type' => 'monthly',
                'salary_rate' => 18250,
            ]
        );

        EmployeeProfile::query()->updateOrCreate(
            ['user_id' => $manager->id],
            [
                'branch_id' => $branch->id,
                'employment_date' => now()->subMonths(12)->toDateString(),
                'employment_type' => 'regular',
                'employment_status' => 'active',
                'salary_type' => 'monthly',
                'salary_rate' => 26500,
            ]
        );

        return [
            'cashier' => $cashier,
            'staff' => $staff,
            'manager' => $manager,
        ];
    }

    private function seedInventoryFlow(Branch $branch, array $products): array
    {
        $stockInService = app(StockInService::class);
        $adjustmentService = app(InventoryAdjustmentService::class);

        $serializedA = $products[0];
        $serializedB = $products[1];
        $accessory = $products[2];

        $stockIn = $stockInService->create([
            'stock_in_number' => $this->nextNumber('STIN', $branch),
            'branch_id' => $branch->id,
            'received_date' => now()->subDays(12)->toDateString(),
            'reference_number' => 'DR-'.$branch->code.'-001',
            'delivery_receipt_number' => 'DEL-'.$branch->code.'-001',
            'remarks' => 'Initial demo branch replenishment',
            'status' => 'pending',
            'items' => [
                [
                    'product_id' => $serializedA->id,
                    'quantity' => 8,
                    'cost_price' => (float) $serializedA->cost_price,
                    'selling_price' => (float) $serializedA->selling_price,
                    'imeis' => [
                        $this->nextImei($branch, $serializedA->id),
                        $this->nextImei($branch, $serializedA->id),
                        $this->nextImei($branch, $serializedA->id),
                        $this->nextImei($branch, $serializedA->id),
                        $this->nextImei($branch, $serializedA->id),
                        $this->nextImei($branch, $serializedA->id),
                        $this->nextImei($branch, $serializedA->id),
                        $this->nextImei($branch, $serializedA->id),
                    ],
                ],
                [
                    'product_id' => $serializedB->id,
                    'quantity' => 3,
                    'cost_price' => (float) $serializedB->cost_price,
                    'selling_price' => (float) $serializedB->selling_price,
                    'imeis' => [
                        $this->nextImei($branch, $serializedB->id),
                        $this->nextImei($branch, $serializedB->id),
                        $this->nextImei($branch, $serializedB->id),
                    ],
                ],
                [
                    'product_id' => $accessory->id,
                    'quantity' => 35,
                    'cost_price' => (float) $accessory->cost_price,
                    'selling_price' => (float) $accessory->selling_price,
                ],
            ],
        ]);

        $stockInService->approve($stockIn);

        $adjustment = $adjustmentService->create([
            'adjustment_number' => $this->nextNumber('ADJ', $branch),
            'branch_id' => $branch->id,
            'reason' => 'Cycle count reconciliation',
            'remarks' => 'Demo shrinkage and correction after count',
            'items' => [
                [
                    'product_id' => $accessory->id,
                    'quantity_after' => 33,
                    'remarks' => 'Two damaged units identified during count',
                ],
            ],
        ]);

        $adjustmentService->approve($adjustment);

        return [
            'stock_in' => $stockIn,
            'transfer_product' => $serializedA,
        ];
    }

    private function seedInventoryTransfers(array $branchSeeds): void
    {
        if (count($branchSeeds) < 2) {
            return;
        }

        $transferService = app(InventoryTransferService::class);
        $origin = $branchSeeds[0]['branch'];

        foreach (array_slice($branchSeeds, 1) as $destinationSeed) {
            /** @var Branch $destination */
            $destination = $destinationSeed['branch'];
            /** @var Product $product */
            $product = $destinationSeed['transfer_product'];

            $transfer = $transferService->create([
                'transfer_number' => $this->nextNumber('TRF', $origin),
                'source_branch_id' => $origin->id,
                'destination_branch_id' => $destination->id,
                'remarks' => 'Demo inter-branch replenishment from main source branch',
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 1,
                        'remarks' => 'Single unit allocation for demand balancing',
                    ],
                ],
            ]);

            $transferService->approve($transfer);
            $transferService->receive($transfer->fresh('items'));
        }
    }

    private function seedPosAndSalesFlow(Branch $branch, User $cashier, $paymentMethods, array $products): void
    {
        Auth::login($cashier);

        $salesService = app(SalesService::class);
        $cashMethod = $paymentMethods->get('cash') ?? PaymentMethod::query()->firstOrFail();
        $gcashMethod = $paymentMethods->get('gcash') ?? $cashMethod;

        $serializedProduct = $products[0];
        $accessoryProduct = $products[2];
        $imei = ProductImei::query()
            ->where('branch_id', $branch->id)
            ->where('product_id', $serializedProduct->id)
            ->where('status', 'available')
            ->orderBy('id')
            ->first();

        if (! $imei) {
            throw new \RuntimeException('No available IMEI found for serialized demo sales in branch '.$branch->id);
        }

        $salesService->hold([
            'branch_id' => $branch->id,
            'remarks' => 'Customer requested quotation hold before final checkout',
            'items' => [
                [
                    'product_id' => $accessoryProduct->id,
                    'quantity' => 2,
                ],
            ],
        ]);

        $salesService->create([
            'sales_number' => $this->nextNumber('SAL', $branch),
            'branch_id' => $branch->id,
            'remarks' => 'Walk-in purchase: handset and charger',
            'items' => [
                [
                    'product_id' => $serializedProduct->id,
                    'quantity' => 1,
                    'imei_id' => $imei->id,
                ],
                [
                    'product_id' => $accessoryProduct->id,
                    'quantity' => 1,
                ],
            ],
            'payments' => [
                [
                    'payment_method_id' => $cashMethod->id,
                    'amount' => (float) $serializedProduct->selling_price + (float) $accessoryProduct->selling_price,
                    'payment_reference' => null,
                ],
            ],
        ]);

        $salesService->create([
            'sales_number' => $this->nextNumber('SAL', $branch),
            'branch_id' => $branch->id,
            'remarks' => 'Split-tender accessory purchase',
            'items' => [
                [
                    'product_id' => $accessoryProduct->id,
                    'quantity' => 3,
                ],
            ],
            'allow_partial' => true,
            'payments' => [
                [
                    'payment_method_id' => $cashMethod->id,
                    'amount' => 500,
                    'payment_reference' => null,
                ],
                [
                    'payment_method_id' => $gcashMethod->id,
                    'amount' => ((float) $accessoryProduct->selling_price * 3) - 500,
                    'payment_reference' => 'GC-'.now()->format('YmdHis').$branch->id,
                ],
            ],
        ]);

        Auth::login($this->actor);
    }

    private function seedAirtimeFlow(Branch $branch, User $cashier, $providers, $paymentMethods): void
    {
        $walletService = app(AirtimeWalletService::class);
        $fundingService = app(AirtimeFundingService::class);
        $adjustmentService = app(AirtimeWalletAdjustmentService::class);
        $transactionService = app(AirtimeTransactionService::class);

        $cashMethod = $paymentMethods->get('cash') ?? PaymentMethod::query()->firstOrFail();

        foreach ($providers->take(2) as $providerIndex => $provider) {
            $wallet = $walletService->paginate(['branch_id' => $branch->id, 'provider_id' => $provider->id])->first();

            if (! $wallet) {
                $wallet = $walletService->create([
                    'wallet_number' => $this->nextNumber('WLT', $branch),
                    'branch_id' => $branch->id,
                    'provider_id' => $provider->id,
                    'beginning_balance' => 15000,
                    'low_balance_threshold' => 2500,
                    'status' => 'active',
                ]);
            }

            $funding = $fundingService->request([
                'wallet_id' => $wallet->id,
                'amount' => 3500,
                'funding_date' => now()->subDays(7)->toDateString(),
                'payment_method' => 'Bank Transfer',
                'reference_number' => 'AF-REF-'.$branch->id.'-'.$provider->id,
                'remarks' => 'Weekly airtime top-up for demo flow',
            ]);
            $fundingService->approve($funding);

            $adjustment = $adjustmentService->request([
                'wallet_id' => $wallet->id,
                'adjustment_type' => 'increase',
                'amount' => 450,
                'reason' => 'Provider cashback adjustment',
                'remarks' => 'Demo wallet correction',
            ]);
            $adjustmentService->approve($adjustment, 'Approved during demo data setup');

            Auth::login($cashier);

            $transactionService->create([
                'transaction_number' => $this->nextNumber('ATX', $branch),
                'branch_id' => $branch->id,
                'provider_id' => $provider->id,
                'wallet_id' => $wallet->id,
                'customer_mobile_number' => '0917'.str_pad((string) (6100000 + ($branch->id * 10) + $providerIndex), 7, '0', STR_PAD_LEFT),
                'load_amount' => 300,
                'payment_method_id' => $cashMethod->id,
                'payment_reference' => null,
                'transaction_status' => 'successful',
                'remarks' => 'Counter load transaction',
            ]);

            $transactionService->create([
                'transaction_number' => $this->nextNumber('ATX', $branch),
                'branch_id' => $branch->id,
                'provider_id' => $provider->id,
                'wallet_id' => $wallet->id,
                'customer_mobile_number' => '0917'.str_pad((string) (6200000 + ($branch->id * 10) + $providerIndex), 7, '0', STR_PAD_LEFT),
                'load_amount' => 500,
                'payment_method_id' => $cashMethod->id,
                'payment_reference' => null,
                'transaction_status' => 'successful',
                'remarks' => 'Repeat customer load',
            ]);

            Auth::login($this->actor);
        }
    }

    private function seedHrFlow(Branch $branch, array $users, PayrollService $payrollService): void
    {
        $scheduleService = app(EmployeeScheduleService::class);
        $attendanceService = app(AttendanceLogService::class);
        $leaveService = app(LeaveRequestService::class);
        $overtimeService = app(OvertimeRequestService::class);
        $cashAdvanceService = app(CashAdvanceService::class);
        $loanService = app(LoanService::class);

        $dateFrom = now()->subDays(14)->toDateString();
        $dateTo = now()->subDay()->toDateString();

        foreach ([$users['cashier'], $users['staff']] as $employee) {
            $scheduleService->createForDateRange([
                'user_id' => $employee->id,
                'branch_id' => $branch->id,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'weekdays' => [1, 2, 3, 4, 5, 6],
                'schedule_type' => 'fixed',
                'time_in' => '09:00',
                'time_out' => '18:00',
                'break_start' => '12:00',
                'break_end' => '13:00',
                'is_rest_day' => false,
            ]);
        }

        foreach ([$users['cashier'], $users['staff']] as $employee) {
            for ($i = 10; $i >= 2; $i--) {
                $attendanceDate = now()->subDays($i)->toDateString();

                if ((int) Carbon::parse($attendanceDate)->dayOfWeek === 0) {
                    continue;
                }

                $alreadyExists = \App\Models\AttendanceLog::query()
                    ->where('user_id', $employee->id)
                    ->whereDate('attendance_date', $attendanceDate)
                    ->exists();

                if ($alreadyExists) {
                    continue;
                }

                $timeIn = Carbon::parse($attendanceDate.' 09:05:00');
                $timeOut = Carbon::parse($attendanceDate.' 18:20:00');

                $attendanceService->create([
                    'user_id' => $employee->id,
                    'branch_id' => $branch->id,
                    'attendance_date' => $attendanceDate,
                    'time_in' => $timeIn,
                    'time_out' => $timeOut,
                    'device_info_in' => 'Android POS Tablet',
                    'device_info_out' => 'Android POS Tablet',
                    'gps_latitude_in' => '15.4414000',
                    'gps_longitude_in' => '120.7287000',
                    'gps_latitude_out' => '15.4415000',
                    'gps_longitude_out' => '120.7288000',
                    'late_minutes' => 5,
                    'undertime_minutes' => 0,
                    'overtime_minutes' => 20,
                    'attendance_status' => 'present',
                ]);
            }
        }

        $leave = $leaveService->create([
            'user_id' => $users['staff']->id,
            'branch_id' => $branch->id,
            'leave_type' => 'vacation_leave',
            'start_date' => now()->addDays(4)->toDateString(),
            'end_date' => now()->addDays(5)->toDateString(),
            'reason' => 'Pre-filed family activity leave',
        ]);

        $leaveService->update($leave, [
            'user_id' => $users['staff']->id,
            'branch_id' => $branch->id,
            'leave_type' => 'vacation_leave',
            'start_date' => $leave->start_date->toDateString(),
            'end_date' => $leave->end_date->toDateString(),
            'reason' => $leave->reason,
            'status' => 'approved',
            'manager_reviewer_id' => $users['manager']->id,
            'manager_reviewed_at' => now()->subDays(1),
            'hr_reviewer_id' => $this->actor->id,
            'hr_reviewed_at' => now(),
            'final_remarks' => 'Approved for planned leave schedule',
        ]);

        $overtime = $overtimeService->create([
            'user_id' => $users['cashier']->id,
            'branch_id' => $branch->id,
            'overtime_date' => now()->subDays(3)->toDateString(),
            'hours' => 2.5,
            'reason' => 'End-of-day inventory recount support',
        ]);

        Auth::login($users['manager']);
        $overtimeService->review($overtime, 'approve');
        Auth::login($this->actor);
        $overtimeService->review($overtime->fresh(), 'approve');

        $advance = $cashAdvanceService->create([
            'user_id' => $users['staff']->id,
            'branch_id' => $branch->id,
            'amount' => 1800,
            'request_date' => now()->subDays(6)->toDateString(),
            'reason' => 'Emergency personal expense',
        ]);
        $cashAdvanceService->approve($advance);
        $cashAdvanceService->release($advance->fresh());

        $loan = $loanService->create([
            'user_id' => $users['cashier']->id,
            'branch_id' => $branch->id,
            'loan_type' => 'salary_loan',
            'principal_amount' => 12000,
            'interest_rate' => 0.06,
            'installment_amount' => 2120,
            'term_months' => 6,
            'start_date' => now()->startOfMonth()->toDateString(),
            'maturity_date' => now()->startOfMonth()->addMonths(6)->toDateString(),
        ]);
        $loanService->approve($loan);
        $loanService->release($loan->fresh());

        $periodStart = now()->startOfMonth();
        $periodEnd = now()->subDay()->endOfDay();

        $period = PayrollPeriod::query()->updateOrCreate(
            ['period_code' => 'DEMO-'.now()->format('Ym').'-'.str_pad((string) $branch->id, 2, '0', STR_PAD_LEFT)],
            [
                'payroll_period_type' => 'semi_monthly',
                'period_start' => $periodStart->toDateString(),
                'period_end' => $periodEnd->toDateString(),
                'status' => 'open',
                'created_by' => $this->actor->id,
            ]
        );

        $run = $payrollService->generateRun($period, $branch->id, $this->actor);
        $payrollService->submitForApproval($run, $this->actor);
        $payrollService->approve($run->fresh(), $users['manager']);
        $payrollService->approve($run->fresh(), $this->actor);
        $payrollService->release($run->fresh(), $this->actor);
    }

    private function seedFinanceFlow(Branch $branch, User $manager, $paymentMethods, ExpenseCategory $expenseCategory): void
    {
        $expenseService = app(ExpenseService::class);
        $expenseApprovalService = app(ExpenseApprovalService::class);

        Auth::login($manager);

        $expense = $expenseService->create([
            'expense_number' => $this->nextNumber('EXP', $branch),
            'branch_id' => $branch->id,
            'category_id' => $expenseCategory->id,
            'expense_date' => now()->subDays(2)->toDateString(),
            'vendor_or_payee' => 'Branch Utilities Provider',
            'amount' => 1250,
            'payment_method_id' => ($paymentMethods->get('cash') ?? PaymentMethod::query()->firstOrFail())->id,
            'description' => 'Operational utilities reimbursement',
            'status' => 'pending',
            'remarks' => 'Demo finance approval flow',
        ]);

        Auth::login($this->actor);
        $expenseApprovalService->approve($expense->fresh());
    }

    private function seedFundTransfers($branches, $paymentMethods): void
    {
        if ($branches->count() < 2) {
            return;
        }

        $fundTransferService = app(FundTransferService::class);
        $source = $branches->first();
        $destination = $branches->last();

        $transfer = $fundTransferService->request([
            'source_branch_id' => $source->id,
            'destination_branch_id' => $destination->id,
            'amount' => 8000,
            'transfer_method' => ($paymentMethods->get('bank transfer')->payment_method_name ?? 'Bank Transfer'),
            'reference_number' => 'FT-DEMO-'.$source->id.'-'.$destination->id,
            'remarks' => 'Demo replenishment transfer between branches',
        ]);

        $fundTransferService->approve($transfer);
    }

    private function seedStatutoryTables(): void
    {
        $effectiveDate = now()->startOfYear()->toDateString();

        SssContributionTable::query()->updateOrCreate(
            [
                'effective_date' => $effectiveDate,
                'salary_from' => 0,
                'salary_to' => 50000,
            ],
            [
                'msc' => 25000,
                'employer_share' => 850,
                'employee_share' => 450,
            ]
        );

        PhilhealthContributionTable::query()->updateOrCreate(
            [
                'effective_date' => $effectiveDate,
                'salary_from' => 0,
                'salary_to' => 50000,
            ],
            [
                'premium_rate' => 0.05,
                'employer_share' => 450,
                'employee_share' => 450,
            ]
        );

        PagibigContributionTable::query()->updateOrCreate(
            [
                'effective_date' => $effectiveDate,
                'salary_from' => 0,
                'salary_to' => 50000,
            ],
            [
                'employee_rate' => 0.02,
                'employer_rate' => 0.02,
                'employee_share' => 100,
                'employer_share' => 100,
            ]
        );

        WithholdingTaxTable::query()->updateOrCreate(
            [
                'effective_date' => $effectiveDate,
                'payroll_period_type' => 'semi_monthly',
                'taxable_income_from' => 0,
                'taxable_income_to' => 100000,
            ],
            [
                'base_tax' => 0,
                'excess_over' => 0,
                'tax_rate' => 0.08,
            ]
        );
    }

    private function nextNumber(string $prefix, Branch $branch): string
    {
        $branchCode = Str::upper((string) ($branch->code ?? $branch->branch_code ?? 'BR'.$branch->id));
        $key = $prefix.'-'.$branchCode;
        $this->numberCounters[$key] = ($this->numberCounters[$key] ?? 0) + 1;

        return sprintf('%s-%s-%s-%03d', $prefix, $branchCode, now()->format('ymdHis'), $this->numberCounters[$key]);
    }

    private function nextImei(Branch $branch, int $productId): string
    {
        do {
            $candidate = '86'.str_pad((string) random_int(0, 9999999999999), 13, '0', STR_PAD_LEFT);
        } while (ProductImei::query()->where('imei_number', $candidate)->exists());

        return $candidate;
    }
}
