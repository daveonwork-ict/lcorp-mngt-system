# Full Demo Operations Seeder

This seeder creates realistic end-to-end operational data across all active branches.

## Seeder Class

- `Database\\Seeders\\FullDemoOperationsSeeder`

## What It Seeds

- Inventory: stock-in (approved), inventory adjustments, inter-branch transfers (approve and receive)
- POS/Sales: held transactions, completed sales, split-tender payments
- Airtime: wallets, wallet fundings (approved), wallet adjustments (approved), load transactions
- HRMS: employee profiles, schedules, attendance logs, leave requests, overtime approvals, cash advances, loans
- Payroll: payroll period, payroll run generation, approval flow, release, and payslip generation
- Finance: expenses (submitted and approved), fund transfers (requested and approved)

All records are created through service-layer workflows so inventory, ledger, and audit side effects are included.

## How To Run

Run only the full operations seeder:

```bash
php artisan db:seed --class=FullDemoOperationsSeeder --no-interaction
```

Run seeding and verification in one step:

```bash
php artisan demo:seed-and-verify
```

Run it automatically with the default seeding flow:

```bash
# Windows PowerShell
$env:RMS_SEED_FULL_DEMO = "1"
php artisan db:seed --no-interaction
```

## Demo User Password

Created demo branch users use this password:

- `Demo@123456`

Emails are generated per branch with these patterns:

- `demo.cashier.<branch-token>@rcstore.local`
- `demo.staff.<branch-token>@rcstore.local`
- `demo.manager.<branch-token>@rcstore.local`

## Post-Seed Verification

After seeding, run:

```bash
php artisan demo:verify-operations
```

For machine-readable output:

```bash
php artisan demo:verify-operations --json
```

For one-shot machine-readable output:

```bash
php artisan demo:seed-and-verify --json
```

This reports per-branch counts and totals for inventory, POS/sales, airtime, HRMS, payroll, and finance records created by the demo flow.
