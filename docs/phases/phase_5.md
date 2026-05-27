````markdown
# RC STORE RMS — PHASE 5
# CASH FLOW, EXPENSES & DAILY CLOSING
# BUILD → VALIDATE → FIX → GATEWAY PROMPT

STRICTLY FOLLOW global_master.md.

DO NOT SKIP.
DO NOT HALLUCINATE.
DO NOT CREATE PLACEHOLDER IMPLEMENTATIONS.
DO NOT BREAK EXISTING LOGIC.
DO NOT HARD CODE VALUES.
DO NOT IGNORE MULTI-BRANCH SUPPORT.
DO NOT IGNORE RESPONSIVENESS.
DO NOT IGNORE SECURITY.
DO NOT IGNORE AUDIT LOGGING.
DO NOT IGNORE POS AND AIRTIME INTEGRATION.

---

# PHASE OBJECTIVE

Build the complete Cash Flow, Expenses, and Daily Closing Module for RC Store RMS.

This phase must centralize all branch-level financial activities including:

- Opening cash
- Cash-in
- Cash-out
- Product sales cash
- Airtime sales cash
- Expenses
- Fund transfers
- Receipt uploads
- Daily closing
- Cash denomination
- Cash variance
- Financial reports

This module depends on:

- Phase 1 — User, Roles, Permissions & Branch Management
- Phase 3 — POS & Sales Management
- Phase 4 — Digital Load / Airtime Management

---

# BUILD SECTION

## 1. CASH FLOW DASHBOARD

Create a cash flow dashboard.

Dashboard cards:

- Today’s cash-in
- Today’s cash-out
- Expected cash
- Actual cash
- Cash variance
- Pending expenses
- Approved expenses
- Branch cash position
- Pending daily closing
- Fund transfers

Charts:

- Daily cash-in vs cash-out
- Expense trend
- Cash variance trend
- Branch cash position
- Expense by category

Tables:

- Recent cash-in
- Recent cash-out
- Pending expenses
- Pending closing reports
- Recent cash variances

---

## 2. OPENING CASH MANAGEMENT

Build opening cash process.

Process flow:

```text
Cashier / Branch Manager Opens Branch Shift
→ Select Branch
→ Encode Opening Cash
→ Add Remarks if Needed
→ Submit Opening Cash
→ System Locks Opening Cash for Active Shift
→ Branch Daily Operation Starts
```

Opening cash fields:

- opening_number
- branch_id
- cashier_id
- opening_date
- opening_time
- opening_cash_amount
- remarks
- status
- encoded_by

Statuses:

- Open
- Closed
- Cancelled

Rules:

- Only one active opening cash per cashier/branch/day unless authorized
- Opening cash cannot be edited after transactions begin unless approved
- Opening cash must be included in daily closing
- Opening cash must be audit logged

---

## 3. CASH-IN MANAGEMENT

Build cash-in recording.

Cash-in sources:

- Product sales
- Airtime/load sales
- Collections
- Other income
- Fund transfer received

Cash-in fields:

- cash_in_number
- branch_id
- source_type
- source_reference_id nullable
- amount
- payment_method_id
- received_by
- received_at
- remarks
- status

Rules:

- POS sales must automatically create or prepare cash-in records
- Airtime transactions must automatically create or prepare cash-in records
- Manual cash-in requires permission
- Cash-in must be linked to source transaction if applicable
- Cash-in must be audit logged

---

## 4. CASH-OUT MANAGEMENT

Build cash-out recording.

Cash-out sources:

- Expenses
- Salary
- Gas
- Utilities
- Office supplies
- Load wallet funding
- Repairs
- Maintenance
- Miscellaneous
- Fund transfer out

Cash-out fields:

- cash_out_number
- branch_id
- source_type
- source_reference_id nullable
- amount
- payment_method_id
- released_by
- released_at
- remarks
- status

Rules:

- Approved expenses must create cash-out records
- Wallet funding may create cash-out or fund transfer reference
- Manual cash-out requires permission
- Cash-out must be audit logged

---

## 5. EXPENSE CATEGORY MANAGEMENT

Create expense category setup.

Default categories:

- Salary
- Gas
- Electricity
- Water
- Internet
- Rent
- Load Wallet Funding
- Office Supplies
- Repairs and Maintenance
- Delivery
- Marketing
- Miscellaneous

Category fields:

- category_code
- category_name
- description
- requires_approval
- monthly_budget_limit nullable
- status

Features:

- Create category
- Edit category
- Activate/deactivate category
- Set approval requirement
- Set monthly budget limit

---

## 6. EXPENSE RECORDING

Build expense recording workflow.

Process flow:

```text
Expense Occurs
→ Staff Encodes Expense
→ Select Expense Category
→ Enter Amount and Details
→ Upload Receipt/Proof
→ Submit Expense
→ Manager/Owner Reviews
→ Approve or Reject
→ If Approved, Cash-Out Created
→ Expense Appears in Reports
```

Expense fields:

- expense_number
- branch_id
- category_id
- expense_date
- vendor_or_payee
- amount
- payment_method_id
- description
- status
- submitted_by
- approved_by nullable
- approved_at nullable
- rejected_by nullable
- rejected_at nullable
- rejection_reason nullable
- remarks

Statuses:

- Draft
- Pending
- Approved
- Rejected
- Cancelled

Rules:

- Expense amount must be greater than zero
- Receipt upload may be required depending on category
- Approval required based on category or amount threshold
- Rejection must require reason
- Approved expense must create cash-out
- Expense must be audit logged

---

## 7. EXPENSE RECEIPT / PROOF UPLOAD

Support secure receipt/proof upload.

Allowed files:

- JPG
- JPEG
- PNG
- PDF

Rules:

- Validate file type
- Validate file size
- Store securely
- Link file to expense
- Authorized preview/download only
- Do not expose sensitive receipts publicly

Attachment fields:

- expense_id
- file_name
- file_path
- file_type
- file_size
- uploaded_by

---

## 8. EXPENSE APPROVAL WORKFLOW

Build expense approval process.

Process:

```text
Expense Submitted
→ Approver Receives Notification
→ Approver Reviews Expense Details and Receipt
→ Approver Approves or Rejects
→ If Approved, Cash-Out Created
→ If Rejected, Reason Required
→ User Notified
→ Audit Log Created
```

Rules:

- Branch Manager may approve assigned branch expenses if allowed
- Owner may approve all expenses
- User cannot approve own expense unless configured
- Rejected expense does not create cash-out
- Approval must be logged

---

## 9. DAILY CLOSING MANAGEMENT

Build daily branch closing workflow.

Process flow:

```text
End of Day
→ Cashier Starts Closing
→ System Gets Opening Cash
→ System Gets Product Sales
→ System Gets Airtime Sales
→ System Gets Other Cash-In
→ System Gets Cash-Out and Expenses
→ System Computes Expected Cash
→ Cashier Encodes Actual Cash Count
→ System Detects Variance
→ Cashier Adds Remarks
→ Manager Reviews Closing
→ Closing Submitted
→ Owner Dashboard Updated
```

Daily closing fields:

- closing_number
- branch_id
- cashier_id
- closing_date
- opening_cash
- product_sales_cash
- airtime_sales_cash
- other_cash_in
- total_cash_in
- total_cash_out
- expected_cash
- actual_cash
- variance_amount
- variance_type
- remarks
- status
- submitted_by
- reviewed_by nullable
- reviewed_at nullable

Statuses:

- Draft
- Submitted
- Reviewed
- Approved
- Rejected
- Cancelled

Variance types:

- Balanced
- Over
- Short

Rules:

- Daily closing must be branch-based
- Daily closing must compute from system transactions
- Actual cash must be encoded
- Variance explanation required if over/short
- Only authorized users can review/approve closing
- Closing must be audit logged

---

## 10. CASH DENOMINATION COUNT

Build cash denomination counting.

Supported denominations:

- 1000
- 500
- 200
- 100
- 50
- 20
- Coins

Denomination fields:

- daily_closing_id
- denomination
- quantity
- total_amount

Rules:

- System must compute actual cash from denomination count
- Denomination total must match actual cash
- Differences must be highlighted

---

## 11. CASH VARIANCE MANAGEMENT

Build cash variance monitoring.

Variance process:

```text
Expected Cash Compared to Actual Cash
→ System Detects Balanced / Over / Short
→ If Variance Exists, Explanation Required
→ Manager Reviews Variance
→ Owner Notified if Required
→ Resolution Recorded
```

Variance fields:

- daily_closing_id
- branch_id
- cashier_id
- expected_cash
- actual_cash
- variance_amount
- variance_type
- explanation
- resolution_status
- resolved_by nullable
- resolved_at nullable

Resolution statuses:

- Pending
- Under Review
- Resolved
- Unresolved

---

## 12. FUND TRANSFER MONITORING

Build fund transfer monitoring.

Use cases:

- Owner sends fund to branch
- Branch remits cash to owner
- Branch transfers funds for wallet loading
- Inter-branch fund transfer

Fields:

- transfer_number
- source_branch_id nullable
- destination_branch_id nullable
- amount
- transfer_method
- reference_number
- proof_file nullable
- status
- requested_by
- approved_by nullable
- remarks

Statuses:

- Draft
- Pending
- Approved
- Rejected
- Completed
- Cancelled

Rules:

- Approval required if configured
- Proof upload validated
- Cash-in/cash-out entries must be prepared or created as applicable
- Fund transfer must be audit logged

---

## 13. FINANCIAL LEDGER

Build financial ledger.

Ledger types:

- Cash-In
- Cash-Out
- Expense
- Sales Cash
- Airtime Cash
- Fund Transfer
- Adjustment

Ledger fields:

- branch_id
- ledger_type
- reference_type
- reference_id
- amount_in
- amount_out
- running_balance nullable
- description
- performed_by
- created_at

Rules:

- Critical financial movements must create ledger entries
- Ledger must not be manually edited
- Corrections must be posted through adjustment workflow

---

## 14. FINANCIAL REPORTS

Build reports:

- Daily cash report
- Branch cash report
- Cash-in report
- Cash-out report
- Expense report
- Expense category report
- Daily closing report
- Cash variance report
- Fund transfer report
- Financial ledger report

Reports must support:

- Date filter
- Branch filter
- Category filter
- User filter
- Status filter
- Excel export
- PDF export
- Print view

---

# DATABASE REQUIREMENTS

Create or update migrations:

- cash_openings
- cash_ins
- cash_outs
- expense_categories
- expenses
- expense_attachments
- daily_closings
- cash_denominations
- cash_variances
- fund_transfers
- financial_ledgers

Relationships:

- Branch has many cash openings
- Branch has many cash-ins
- Branch has many cash-outs
- Branch has many expenses
- Expense belongs to category
- Expense belongs to branch
- Expense has many attachments
- Daily closing belongs to branch
- Daily closing belongs to cashier/user
- Daily closing has many denominations
- Daily closing has one variance
- Fund transfer may belong to source branch
- Fund transfer may belong to destination branch
- Financial ledger belongs to branch

---

# BACKEND REQUIREMENTS

Create controllers:

- CashFlowDashboardController
- CashOpeningController
- CashInController
- CashOutController
- ExpenseCategoryController
- ExpenseController
- ExpenseApprovalController
- ExpenseAttachmentController
- DailyClosingController
- CashDenominationController
- CashVarianceController
- FundTransferController
- FinancialLedgerController
- FinancialReportController

Create services:

- CashFlowService
- CashOpeningService
- CashInService
- CashOutService
- ExpenseService
- ExpenseApprovalService
- DailyClosingService
- CashVarianceService
- FundTransferService
- FinancialLedgerService
- FinancialReportService

Business logic must be inside services.

Controllers must remain clean.

---

# UI/UX REQUIREMENTS

Create responsive screens:

- Cash flow dashboard
- Opening cash form
- Cash-in list
- Cash-out list
- Expense category list
- Expense create/edit form
- Expense approval page
- Expense details page
- Daily closing form
- Cash denomination form
- Cash variance page
- Fund transfer page
- Financial ledger page
- Financial reports page

UI requirements:

- Summary cards
- Status badges
- Responsive tables
- Branch filters
- Date filters
- Clear approval buttons
- Receipt preview modal
- Warning badge for cash shortage
- Success badge for balanced cash
- Mobile-friendly forms

---

# SECURITY REQUIREMENTS

Implement permissions:

- view_cash_flow
- create_cash_opening
- create_cash_in
- create_cash_out
- view_expenses
- create_expense
- approve_expense
- reject_expense
- view_daily_closing
- submit_daily_closing
- review_daily_closing
- manage_fund_transfers
- view_financial_ledger
- view_financial_reports
- export_financial_reports

Rules:

- Branch users can only access assigned branch financial records
- Owner can view all branches
- Expense approval is restricted
- Daily closing review is restricted
- Fund transfer approval is restricted
- Receipt files are access-controlled
- Unauthorized users cannot view financial records

---

# AUDIT TRAIL REQUIREMENTS

Log:

- Opening cash created
- Cash-in recorded
- Cash-out recorded
- Expense submitted
- Expense approved
- Expense rejected
- Receipt uploaded
- Daily closing submitted
- Daily closing reviewed
- Cash variance detected
- Cash variance resolved
- Fund transfer requested
- Fund transfer approved/rejected
- Financial report exported

Audit must include:

- user_id
- branch_id
- module_name
- action_type
- before_value
- after_value
- ip_address
- user_agent
- created_at

---

# NOTIFICATION REQUIREMENTS

Generate notifications for:

- Expense submitted
- Expense approved/rejected
- Cash shortage detected
- Cash overage detected
- Daily closing submitted
- Daily closing missing
- Fund transfer request
- Fund transfer approved/rejected
- High expense amount
- Cash variance unresolved

---

# VALIDATE SECTION

Validate:

## Cash Opening
- Opening cash creation works
- Duplicate active opening is blocked
- Opening cash locks properly

## Cash-In / Cash-Out
- Cash-in recording works
- Cash-out recording works
- POS integration works
- Airtime integration works
- Ledger is created

## Expenses
- Expense creation works
- Receipt upload works
- Approval works
- Rejection requires reason
- Approved expense creates cash-out

## Daily Closing
- Opening cash is included
- Product sales are included
- Airtime sales are included
- Expenses are included
- Expected cash computes correctly
- Actual cash is recorded
- Variance computes correctly
- Denomination count works

## Reports
- Filters work
- Export works
- Print view works

## Security
- Branch restrictions work
- Permissions work
- Unauthorized access is blocked
- Receipt access is protected

## UI
- Financial pages are responsive
- Tables do not overflow
- Forms are mobile-friendly

---

# FIX SECTION

If issues are found:

- Fix cash computation
- Fix ledger posting
- Fix expense approval logic
- Fix receipt upload validation
- Fix daily closing calculation
- Fix denomination mismatch
- Fix variance calculation
- Fix branch leakage
- Fix permission leaks
- Fix report inaccuracies
- Fix unresponsive financial pages
- Refactor duplicated finance logic

Revalidate after fixing.

---

# GATEWAY REVIEW SECTION

Before marking Phase 5 complete, verify:

- global_master.md is followed
- cash flow dashboard works
- opening cash works
- cash-in works
- cash-out works
- expense workflow works
- receipt upload is secure
- expense approval works
- daily closing works
- denomination count works
- variance detection works
- fund transfer works
- financial ledger works
- reports work
- branch restrictions are enforced
- audit logs are complete
- no incorrect cash computation
- no unauthorized expense approval
- no branch data leakage
- no insecure receipt access
- no broken responsive UI
- no route conflict
- no migration conflict

---

# EXPECTED OUTPUT

At the end of Phase 5, provide:

- Complete cash flow dashboard
- Complete opening cash module
- Complete cash-in module
- Complete cash-out module
- Complete expense category module
- Complete expense recording module
- Complete expense approval workflow
- Complete receipt upload handling
- Complete daily closing module
- Complete cash denomination module
- Complete cash variance module
- Complete fund transfer module
- Complete financial ledger
- Complete financial reports
- POS cash integration
- Airtime cash integration
- Updated migrations
- Updated models
- Updated controllers
- Updated services
- Updated routes
- Updated views
- Updated permissions
- Updated audit logs
- Updated notifications
- Responsive financial UI

PHASE 5 IS NOT COMPLETE UNTIL ALL VALIDATIONS PASS.
````
