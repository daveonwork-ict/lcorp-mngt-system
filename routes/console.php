<?php

use App\Models\Branch;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('demo:verify-operations {--json : Output JSON summary}', function () {
    $branches = Branch::query()
        ->where('is_active', true)
        ->orderBy('id')
        ->get(['id', 'code', 'branch_name', 'name']);

    if ($branches->isEmpty()) {
        $this->warn('No active branches found.');

        return;
    }

    $sumByBranch = static function (string $table, string $branchColumn = 'branch_id', ?callable $query = null): array {
        $builder = DB::table($table)
            ->selectRaw($branchColumn.' as branch_id, COUNT(*) as total')
            ->groupBy($branchColumn);

        if ($query) {
            $query($builder);
        }

        return $builder
            ->pluck('total', 'branch_id')
            ->map(static fn ($value) => (int) $value)
            ->all();
    };

    $stockIns = $sumByBranch('stock_ins', 'branch_id', static fn ($q) => $q->where('status', 'approved'));
    $inventoryMovements = $sumByBranch('inventory_movements');
    $sales = $sumByBranch('sales', 'branch_id', static fn ($q) => $q->where('sales_status', 'completed'));
    $heldTransactions = $sumByBranch('held_transactions');
    $airtimeTransactions = $sumByBranch('airtime_transactions', 'branch_id', static fn ($q) => $q->where('transaction_status', 'successful'));
    $airtimeWallets = $sumByBranch('airtime_wallets', 'branch_id', static fn ($q) => $q->where('status', 'active'));
    $employeeProfiles = $sumByBranch('employee_profiles', 'branch_id', static fn ($q) => $q->where('employment_status', 'active'));
    $employeeSchedules = $sumByBranch('employee_schedules');
    $attendanceLogs = $sumByBranch('attendance_logs');
    $leaveRequests = $sumByBranch('leave_requests', 'branch_id', static fn ($q) => $q->where('status', 'approved'));
    $overtimeRequests = $sumByBranch('overtime_requests', 'branch_id', static fn ($q) => $q->where('status', 'approved'));
    $payrollRuns = $sumByBranch('payroll_runs', 'branch_id', static fn ($q) => $q->where('status', 'released'));
    $payrollItems = $sumByBranch('payroll_items', 'branch_id', static fn ($q) => $q->where('status', 'released'));
    $expenses = $sumByBranch('expenses', 'branch_id', static fn ($q) => $q->where('status', 'approved'));
    $cashIns = $sumByBranch('cash_ins');
    $cashOuts = $sumByBranch('cash_outs');
    $fundOut = $sumByBranch('fund_transfers', 'source_branch_id', static fn ($q) => $q->where('status', 'approved'));
    $fundIn = $sumByBranch('fund_transfers', 'destination_branch_id', static fn ($q) => $q->where('status', 'approved'));

    $rows = $branches->map(function (Branch $branch) use (
        $stockIns,
        $inventoryMovements,
        $sales,
        $heldTransactions,
        $airtimeTransactions,
        $airtimeWallets,
        $employeeProfiles,
        $employeeSchedules,
        $attendanceLogs,
        $leaveRequests,
        $overtimeRequests,
        $payrollRuns,
        $payrollItems,
        $expenses,
        $cashIns,
        $cashOuts,
        $fundOut,
        $fundIn
    ): array {
        return [
            'branch_code' => $branch->code,
            'branch_name' => $branch->branch_name ?: $branch->name,
            'stock_ins_approved' => $stockIns[$branch->id] ?? 0,
            'inventory_movements' => $inventoryMovements[$branch->id] ?? 0,
            'sales_completed' => $sales[$branch->id] ?? 0,
            'held_transactions' => $heldTransactions[$branch->id] ?? 0,
            'airtime_transactions' => $airtimeTransactions[$branch->id] ?? 0,
            'airtime_wallets_active' => $airtimeWallets[$branch->id] ?? 0,
            'employees_active' => $employeeProfiles[$branch->id] ?? 0,
            'schedules' => $employeeSchedules[$branch->id] ?? 0,
            'attendance_logs' => $attendanceLogs[$branch->id] ?? 0,
            'leave_approved' => $leaveRequests[$branch->id] ?? 0,
            'overtime_approved' => $overtimeRequests[$branch->id] ?? 0,
            'payroll_runs_released' => $payrollRuns[$branch->id] ?? 0,
            'payroll_items_released' => $payrollItems[$branch->id] ?? 0,
            'expenses_approved' => $expenses[$branch->id] ?? 0,
            'cash_ins' => $cashIns[$branch->id] ?? 0,
            'cash_outs' => $cashOuts[$branch->id] ?? 0,
            'fund_transfer_out' => $fundOut[$branch->id] ?? 0,
            'fund_transfer_in' => $fundIn[$branch->id] ?? 0,
        ];
    })->all();

    $totals = [
        'branches' => count($rows),
        'stock_ins_approved' => array_sum(array_column($rows, 'stock_ins_approved')),
        'inventory_movements' => array_sum(array_column($rows, 'inventory_movements')),
        'sales_completed' => array_sum(array_column($rows, 'sales_completed')),
        'held_transactions' => array_sum(array_column($rows, 'held_transactions')),
        'airtime_transactions' => array_sum(array_column($rows, 'airtime_transactions')),
        'airtime_wallets_active' => array_sum(array_column($rows, 'airtime_wallets_active')),
        'employees_active' => array_sum(array_column($rows, 'employees_active')),
        'schedules' => array_sum(array_column($rows, 'schedules')),
        'attendance_logs' => array_sum(array_column($rows, 'attendance_logs')),
        'leave_approved' => array_sum(array_column($rows, 'leave_approved')),
        'overtime_approved' => array_sum(array_column($rows, 'overtime_approved')),
        'payroll_runs_released' => array_sum(array_column($rows, 'payroll_runs_released')),
        'payroll_items_released' => array_sum(array_column($rows, 'payroll_items_released')),
        'expenses_approved' => array_sum(array_column($rows, 'expenses_approved')),
        'cash_ins' => array_sum(array_column($rows, 'cash_ins')),
        'cash_outs' => array_sum(array_column($rows, 'cash_outs')),
        'fund_transfer_out' => array_sum(array_column($rows, 'fund_transfer_out')),
        'fund_transfer_in' => array_sum(array_column($rows, 'fund_transfer_in')),
    ];

    if ($this->option('json')) {
        $this->line(json_encode(['rows' => $rows, 'totals' => $totals], JSON_PRETTY_PRINT));

        return;
    }

    $displayRows = array_map(static function (array $row): array {
        return [
            'branch' => $row['branch_code'],
            'sales' => $row['sales_completed'],
            'airtime' => $row['airtime_transactions'],
            'stock_in' => $row['stock_ins_approved'],
            'inv_move' => $row['inventory_movements'],
            'held' => $row['held_transactions'],
            'employees' => $row['employees_active'],
            'sched' => $row['schedules'],
            'attendance' => $row['attendance_logs'],
            'leave' => $row['leave_approved'],
            'ot' => $row['overtime_approved'],
            'payroll' => $row['payroll_runs_released'],
            'expenses' => $row['expenses_approved'],
            'cash_in' => $row['cash_ins'],
            'cash_out' => $row['cash_outs'],
            'fund_out' => $row['fund_transfer_out'],
            'fund_in' => $row['fund_transfer_in'],
        ];
    }, $rows);

    $this->table(array_keys($displayRows[0]), $displayRows);

    $this->newLine();
    $this->info('Totals: '.json_encode($totals));

    $missing = collect($rows)->filter(static function (array $row): bool {
        return $row['sales_completed'] === 0
            || $row['airtime_transactions'] === 0
            || $row['stock_ins_approved'] === 0
            || $row['attendance_logs'] === 0
            || $row['payroll_runs_released'] === 0;
    });

    if ($missing->isEmpty()) {
        $this->info('Coverage check passed: all active branches have core demo operations.');

        return;
    }

    $this->warn('Coverage check warning: some branches have missing core operations.');
    $this->line('Affected branches: '.$missing->pluck('branch_code')->implode(', '));
})->purpose('Verify full demo operations coverage per branch across modules');

Artisan::command('demo:seed-and-verify {--json : Output JSON summary from verification}', function () {
    $this->info('Running full demo operations seeder...');

    $exitCode = $this->call('db:seed', [
        '--class' => 'FullDemoOperationsSeeder',
        '--no-interaction' => true,
    ]);

    if ($exitCode !== 0) {
        $this->error('Seeding failed. Verification skipped.');

        return $exitCode;
    }

    $this->newLine();
    $this->info('Running demo verification...');

    return $this->call('demo:verify-operations', [
        '--json' => (bool) $this->option('json'),
    ]);
})->purpose('Run full demo seeding and verification in one command');
