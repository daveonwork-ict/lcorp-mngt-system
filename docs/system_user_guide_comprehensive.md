# RC Store RMS Comprehensive User Guide

## 1. Purpose of This Guide

This document is a complete operating guide for RC Store RMS across all major modules:

- Dashboard (Executive and Branch)
- POS and Sales
- Inventory and Purchasing
- Airtime / Digital Load
- Cash Flow and Finance
- Warranty and Customers
- HRMS (attendance, schedule, payroll, loans, cash advances)
- Communication (announcements, chat, notification center)
- Reports and exports
- Approvals and Administration
- Security and Deployment support tools

This guide is written for daily operations, supervisory controls, and end-to-end scenario execution.

## 2. Who Should Use This Guide

- Owners and Executives
- Branch Managers
- Cashiers
- Inventory Staff
- Accounting and Finance Staff
- HR Staff
- Warranty and Customer Service Staff
- Admin / Super Admin / Auditors

## 3. System Access and Login

### 3.1 Login URL

- Login page: `/login`

### 3.2 Password Recovery

- Forgot password flow is available at `/forgot-password`.

### 3.3 Session and Security

- Authenticated pages run under `auth`, `branch.access`, and `session.track` middleware.
- Branch access and role permission restrictions apply on every module route.

### 3.4 Default Accounts

For current default and demo users, refer to:

- `docs/default_user_credentials.md`

## 4. Core Operating Concepts

### 4.1 Branch Scope

- Most records are branch-bound.
- Non-global roles only see their accessible branches.
- Owner/Super Admin roles can view cross-branch data when allowed.

### 4.2 Approval-Driven Transactions

Several modules use create -> review -> approve/reject workflows:

- Sales void requests
- Sales returns
- Wallet funding / wallet adjustments
- Expenses
- Inventory transfer and adjustment
- Overtime and payroll approvals
- Fund transfers

### 4.3 Auditability

Critical actions are audit-logged (financial and operational controls).

### 4.4 Report Exports

Most report modules support CSV, Excel, print, or PDF export depending on permission.

## 5. Navigation Map (High-Level)

### 5.1 Dashboards

- Executive dashboard: `/dashboard/owner`
- Branch dashboard: `/dashboard/branch`
- Branch switch action: `/branch/switch`

### 5.2 Module Entry Points

- POS: `/pos`
- Inventory dashboard: `/inventory` and `/inventory/dashboard`
- Airtime dashboard: `/airtime` and `/airtime/dashboard`
- Cash flow dashboard: `/cash-flow`
- Warranty dashboard: `/warranty`
- HR dashboard: `/hr/dashboard`
- Communication dashboard: `/communication`
- Reports hub: `/reports`
- Approvals inbox: `/approvals`

## 6. Detailed Module Operations

## 6A. Dashboard Module

### 6A.1 Executive Dashboard

URL: `/dashboard/owner`

Primary use:

- Multi-branch KPI monitoring
- Cross-module trend visibility
- Alert visibility and branch-level performance snapshots

Typical actions:

1. Set date filters and branch filters.
2. Review top KPIs (sales, expenses, net estimates, inventory value, airtime totals, pending approvals).
3. Drill down to module-specific pages from side navigation.

### 6A.2 Branch Dashboard

URL: `/dashboard/branch`

Primary use:

- Branch-specific operating metrics
- Daily branch decision support

Typical actions:

1. Confirm active branch selection.
2. Review branch-level snapshots.
3. Proceed to transaction modules for execution.

## 6B. POS and Sales Module

### 6B.1 POS Checkout

URL: `/pos`

Core workflow:

1. Search and tap products.
2. Build cart and adjust quantities.
3. Apply discounts (if permitted).
4. Select payment method and enter payment details.
5. Checkout.

Key route operations:

- Checkout: `POST /pos/checkout`

### 6B.2 Sales Listing and Details

- Sales list: `/sales`
- Sales detail: `/sales/{sale}`
- Receipt: `/sales/{sale}/receipt`
- Reprint receipt: `/sales/{sale}/receipt/reprint`

### 6B.3 Held Transactions

- List and create holds: `/sales/held-transactions`
- Resume hold: `POST /sales/held-transactions/{heldTransaction}/resume`
- Cancel hold: `POST /sales/held-transactions/{heldTransaction}/cancel`

### 6B.4 Sale Voids

- Void requests list: `/sales/void-requests`
- Create request: `POST /sales/{sale}/void-requests`
- Approve/reject request: respective approval routes

### 6B.5 Sales Returns

- Returns index/create: `/sales/returns`
- Approve/reject return: route-level approval actions

### 6B.6 Sales Dashboard

- URL: `/sales-dashboard`

## 6C. Inventory and Purchasing Module

### 6C.1 Product Master Data

- Categories: `/inventory/categories`
- Brands: `/inventory/brands`
- Products: `/inventory/products`
- Price history: `/inventory/price-histories`
- IMEI tracking: `/inventory/imeis`

### 6C.2 Stock In

- List and view: `/inventory/stock-ins`
- Create: `/inventory/stock-ins/create`
- Approve flow: `POST /inventory/stock-ins/{stockIn}/approve`

### 6C.3 Inventory Adjustments

- List: `/inventory/adjustments`
- Create: `/inventory/adjustments/create`
- Approve: `POST /inventory/adjustments/{adjustment}/approve`

### 6C.4 Branch Transfers

- List: `/inventory/transfers`
- Create: `/inventory/transfers/create`
- Approve and receive: dedicated transfer routes

### 6C.5 Physical Counts

- List/create: `/inventory/physical-counts`
- Submit and generate adjustments: physical count action routes

### 6C.6 Inventory Analytics and Control

- Branch inventory: `/inventory/branch-inventory`
- Movements: `/inventory/movements`
- Alerts and resolve: `/inventory/alerts`

### 6C.7 Purchasing

Primary sections:

- Dashboard: `/purchasing`
- Purchase requests: `/purchasing/requests`
- Purchase orders: `/purchasing/orders`
- Receiving reports: `/purchasing/receiving-reports`
- Payables: `/purchasing/payables`
- Payments: `/purchasing/payments`
- Purchasing reports: `/purchasing/reports`

## 6D. Airtime / Digital Load Module

### 6D.1 Airtime Dashboard

- `/airtime` or `/airtime/dashboard`

### 6D.2 Providers, Wallets, Ledgers

- Providers: `/airtime/providers`
- Wallets: `/airtime/wallets`
- Ledgers: `/airtime/ledgers`

### 6D.3 Wallet Fundings

- Index/create: `/airtime/fundings`
- Approve/reject: funding approval routes

### 6D.4 Airtime Transactions

- List/create: `/airtime/transactions`
- Detail: `/airtime/transactions/{transaction}`
- Reverse transaction: `POST /airtime/transactions/{transaction}/reverse`

### 6D.5 Wallet Adjustments

- Index/create: `/airtime/adjustments`
- Approve/reject: adjustment approval routes

### 6D.6 Airtime Reports

- Reports page: `/airtime/reports`
- CSV export: `/airtime/reports/export-csv`
- Print view: `/airtime/reports/print`

## 6E. Cash Flow and Finance Module

### 6E.1 Cash Flow Dashboard

- `/cash-flow`

### 6E.2 Cash Opening, Cash In, Cash Out

- Openings: `/cash-flow/openings`
- Cash ins: `/cash-flow/cash-ins`
- Cash outs: `/cash-flow/cash-outs`

### 6E.3 Expenses

- Expenses index/detail: `/expenses`
- Create, approve, reject via expense routes
- Categories: `/expenses/categories`

### 6E.4 Daily Closing and Variances

- Daily closing: `/cash-flow/daily-closing`
- Variances: `/cash-flow/variances`

### 6E.5 Fund Transfers

- `/cash-flow/transfers`

### 6E.6 Financial Ledger and Reports

- Ledger: `/cash-flow/ledger`
- Reports: `/cash-flow/reports`
- Export: CSV, Excel, PDF, and print routes

## 6F. Warranty and Customer Module

### 6F.1 Customers

- Customers list: `/customers`
- Customer profile: `/customers/{customer}/profile`
- Deactivate and note routes are available under customer actions

### 6F.2 Warranty Core

- Dashboard: `/warranty`
- Records list/detail: `/warranty/records`
- Warranty lookup: `/warranty/lookup`

### 6F.3 Warranty Rules and Claims

- Rules: `/warranty/rules`
- Claims index/create: `/warranty/claims`
- Claim attachments: upload/download routes
- Claim decisions: approve/reject/status-update routes
- Repair and replacement flows: dedicated claim action routes

### 6F.4 Warranty Reporting

- Reports: `/warranty/reports`
- Export CSV/Excel and print available via routes

## 6G. HRMS Module

Module prefix: `/hr/*`

### 6G.1 Employee and Position Management

- Employees: `/hr/employees`
- Positions: `/hr/positions`

### 6G.2 Scheduling

- Schedule list: `/hr/schedules`
- Schedule create/edit and import/template routes available for manage roles

### 6G.3 Attendance

- Attendance records: `/hr/attendance`
- Selfie preview and reverify routes are available

### 6G.4 Leaves and Overtime

- Leaves: `/hr/leaves`
- Overtime: `/hr/overtime`
- Overtime approval/rejection routes available for reviewers

### 6G.5 Payroll

- Payroll periods: `/hr/payroll/periods`
- Payroll runs: `/hr/payroll/runs`
- Generate, submit, approve, release routes available

### 6G.6 Government Contributions

- Contributions: `/hr/contributions`
- SSS, PhilHealth, Pag-IBIG, tax table entry routes

### 6G.7 Employee Financial Assistance

- Cash advances: `/hr/cash-advances`
- Loans: `/hr/loans`

### 6G.8 Payslips and HR Reports

- Payslips: `/hr/payslips`
- HR reports: `/hr/reports` with print and exports

## 6H. Communication Module

### 6H.1 Dashboard and Announcements

- Communication dashboard: `/communication`
- Announcements index/show: `/announcements`

Actions include:

- Read and acknowledge announcements
- Create, edit, publish, archive announcements
- Manage targets and attachments
- Read receipt monitoring

### 6H.2 Chat

- Chat index: `/chat`
- Chat room detail: `/chat/rooms/{room}`
- Room and member management routes
- Message create/edit/delete routes

### 6H.3 Notification Center

- Notification center: `/communication/notifications`
- Mark single/all as read routes

## 6I. Reports Module

### 6I.1 Reports Hub

- `/reports`

### 6I.2 Module Reports

- Sales reports: `/reports/sales`
- Inventory reports: `/reports/inventory`
- Communication reports: `/reports/communication`
- Audit reports: `/reports/audit`
- Audit trail alias: `/audit-trail`

Export and print options depend on permissions.

## 6J. Approvals and Admin Module

### 6J.1 Approvals

- Inbox: `/approvals` and `/approvals/inbox`
- Requests and history routes available
- Approve/reject/return/resubmit actions

### 6J.2 User/Role/Branch Administration

- Users: `/users`
- Roles: `/roles`
- Permissions listing: `/permissions`
- Branch management: `/branches`

### 6J.3 Security and Monitoring

- Activity logs: `/activity-logs`
- Login activity/history routes
- Session security and termination routes
- Security alerts and audit trail exports
- Backup logs and backup run route

### 6J.4 Deployment and Go-Live Support

- Deployment checklists
- Data import center and templates
- Training logs
- Go-live checklist
- Support ticketing
- Acceptance sign-off routes

## 7. End-to-End Scenario-Based Operations

## Scenario 1: Branch Daily Opening to Closing

Role focus: Branch Manager, Cashier, Finance Reviewer

1. Open branch cash float via cash opening route.
2. Cashier executes POS sales and holds/resumes when needed.
3. Airtime transactions are processed throughout the day.
4. Incidental expenses are filed and approved per policy.
5. Branch submits daily closing with denomination breakdown.
6. Reviewer resolves variance if mismatch occurs.
7. Executive monitors KPIs on dashboards.

Expected outcomes:

- Posted cash-ins/outs and complete sales trail
- Reconciled daily closing
- Variance resolution log if needed

## Scenario 2: Inventory Replenishment and Inter-Branch Transfer

Role focus: Inventory Staff, Branch Manager, Receiver

1. Create stock-in for incoming goods.
2. Approve stock-in to post inventory movements.
3. Run physical count and generate adjustment if needed.
4. Create transfer from source branch to destination branch.
5. Approve transfer and mark as received at destination.
6. Validate branch inventory and movement logs.

Expected outcomes:

- Inventory balances updated in both branches
- IMEI and movement trail retained
- Alerts refreshed after stock updates

## Scenario 3: Airtime Wallet Lifecycle

Role focus: Airtime Staff, Approver, Branch Manager

1. Confirm provider and wallet readiness.
2. Request wallet funding for low balance.
3. Approver approves funding.
4. Process customer load transactions.
5. If correction needed, request/approve wallet adjustment.
6. If transaction issue occurs, perform reversal with reason.
7. Review airtime reports and ledgers.

Expected outcomes:

- Correct wallet running balance
- Commission records generated
- Alerting and audit logs maintained

## Scenario 4: HR Attendance to Payroll Release

Role focus: Staff User, HR Staff, Approvers

1. Maintain employee schedules.
2. Record attendance with selfie metadata and GPS/device context.
3. Process leaves and overtime requests with reviewer decisions.
4. Create payroll period.
5. Generate payroll run and review outputs.
6. Submit for approval chain.
7. Release payroll and generate payslips.

Expected outcomes:

- Accurate payroll item computations
- Loan/cash advance deductions applied on release
- Downloadable payslips generated

## Scenario 5: Warranty Claim Processing

Role focus: Customer Service, Warranty Reviewer, Repair Team

1. Lookup customer purchase/warranty record.
2. File warranty claim and upload attachments.
3. Approver approves or rejects claim.
4. If approved, process repair or replacement path.
5. Track final claim status.
6. Export warranty reports for management.

Expected outcomes:

- Complete claim timeline and attachments
- Claim decision traceability
- Status-driven repair/replacement records

## Scenario 6: Month-End Management Reporting

Role focus: Owner, Super Admin, Accounting

1. Run module reports (sales, inventory, airtime, finance, HR, warranty).
2. Apply branch/date filters for comparative analysis.
3. Export CSV/Excel/PDF and print packets.
4. Review unresolved approvals, alerts, and variances.
5. Finalize action items for next period.

Expected outcomes:

- Consolidated management report package
- Pending risks identified
- Branch performance benchmarked

## 8. Operational Best Practices

- Always confirm branch filter before creating transactions.
- Use approval queues daily; avoid approval backlog.
- Reconcile cash flow and daily closing at end of day.
- Resolve inventory and security alerts promptly.
- Use report exports for period snapshots before data changes.
- For HR, ensure attendance completeness before payroll generation.
- For airtime, monitor low-balance thresholds proactively.

## 9. Permission and Governance Guidance

- Assign only minimum required permissions per role.
- Keep branch-level access constrained for non-global roles.
- Use admin/security routes to monitor unusual behavior.
- Review active sessions and terminate suspicious sessions immediately.

## 10. Troubleshooting Guide

### 10.1 Access Denied / Forbidden

Possible causes:

- Missing permission
- Branch access restriction
- Inactive role/user

Actions:

1. Confirm role permission set.
2. Confirm user branch assignment.
3. Check if user is active and linked to expected branch.

### 10.2 Data Not Showing in List

Possible causes:

- Date or branch filters too restrictive
- Status filter mismatch
- Role-scoped visibility

Actions:

1. Clear filters and reapply one by one.
2. Verify branch context.
3. Confirm user permission scope.

### 10.3 Transaction Cannot Be Approved

Possible causes:

- Not in approvable status
- Missing approval permission
- Branch scope mismatch

Actions:

1. Check current transaction status.
2. Validate approver role and permission.
3. Review branch and ownership constraints.

## 11. Reference Documents

- Default credentials: `docs/default_user_credentials.md`
- Employee self-service test users: `docs/employee_self_service_test_accounts.md`
- Full demo seeding guide: `docs/full_demo_operations_seeder.md`

## 12. Suggested Team SOP Cadence

Daily:

- Cash opening/closing checks
- Approval queue processing
- Low stock and wallet alert checks

Weekly:

- Inventory transfer and replenishment review
- Expense and variance trend review
- HR attendance/overtime exception review

Monthly:

- Full report export and executive review
- Permission/role audit and session/security review
- Branch performance and improvement planning
