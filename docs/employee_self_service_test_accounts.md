# Employee Self-Service Test Accounts

This note records the live employee self-service accounts prepared in the local MySQL environment on 2026-05-30.

These accounts are intended for smoke testing the HR self-service flow:

- login by username or email
- attendance selfie in/out
- leave requests
- overtime requests
- payslip viewing
- employee dashboard chat activity
- employee dashboard communication notifications

## Shared Password

All employee self-service test accounts currently use:

`Emp@123456`

## Active Accounts

| Branch Code | Branch Name | Username | Email | Payslip Number |
| --- | --- | --- | --- | --- |
| MAIN | Head Office - Main Warehouse | `employee.self` | `employee.self@rcstore.local` | `PS-20260530051218-00001` |
| RCS-LPZ | RC Station - La Paz Branch | `employee.rcslpz` | `employee.rcslpz@rcstore.local` | `PS-20260530052229-00002` |
| RCS-ZRG | RC Station - Zaragoza Branch | `employee.rcszrg` | `employee.rcszrg@rcstore.local` | `PS-20260530052229-00003` |
| GB-CPS | Gadgets & Beauty - Capas Branch | `employee.gbcps` | `employee.gbcps@rcstore.local` | `PS-20260530052230-00004` |
| KRS-TARLAC | Kriscielo - Tarlac Branch / Magic Star Mall | `employee.krstarlac` | `employee.krstarlac@rcstore.local` | `PS-20260530052230-00005` |

## Notes

- All accounts are assigned the `staff_user` role.
- Each account is linked to its branch through `primary_branch_id` and `user_branches`.
- Each account has an `employee_profiles` record.
- Payslip files are stored under `storage/app/hr/payslips/`.
- Live dashboard communication data can be recreated with `php artisan db:seed --class=EmployeeDashboardCommunicationSeeder --no-interaction`.
- The communication seed creates one private support room, one starter message, and one unread communication notification per employee account.
- The local app server was validated at `http://127.0.0.1:8000` during setup.

## Quick Validation Targets

- Login page: `http://127.0.0.1:8000/login`
- Payslips page after login: `http://127.0.0.1:8000/hr/payslips`
- Attendance page after login: `http://127.0.0.1:8000/hr/attendance`
- Leave page after login: `http://127.0.0.1:8000/hr/leaves`
- Overtime page after login: `http://127.0.0.1:8000/hr/overtime`