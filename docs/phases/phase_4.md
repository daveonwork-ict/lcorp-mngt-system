````markdown
# RC STORE RMS — PHASE 4
# DIGITAL LOAD / AIRTIME MANAGEMENT
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
DO NOT IGNORE CASH FLOW INTEGRATION.

---

# PHASE OBJECTIVE

Build the complete Digital Load / Airtime Management Module for RC Store RMS.

This phase must replace the existing manual Excel-based airtime monitoring with a centralized, branch-based digital load monitoring module.

The module must support:

- Airtime provider setup
- Branch wallet monitoring
- Wallet funding
- Load sales transactions
- Auto wallet deduction
- Commission tracking
- Failed/reversed transaction handling
- Airtime reporting
- Owner monitoring dashboard

This module depends on:

- Phase 1 — User, Roles, Permissions & Branch Management
- Phase 5 — Cash Flow integration readiness

---

# BUILD SECTION

## 1. AIRTIME PROVIDER MANAGEMENT

Create airtime provider management.

Supported providers should be database-driven, not hardcoded.

Suggested providers:

- Smart
- TNT
- Globe
- TM
- DITO
- Other Provider

Provider fields:

- provider_code
- provider_name
- description
- logo nullable
- default_commission_type
- default_commission_value
- status

Commission types:

- Fixed
- Percentage
- None

Features:

- Create provider
- Edit provider
- View provider
- Activate/deactivate provider
- Configure default commission

---

## 2. BRANCH WALLET MANAGEMENT

Create branch-based airtime wallet management.

Each branch may have wallet balance per provider.

Wallet fields:

- wallet_number
- branch_id
- provider_id
- beginning_balance
- current_balance
- low_balance_threshold
- status
- created_by
- updated_by

Rules:

- Wallet must be unique per branch and provider
- Wallet balance cannot be negative
- Wallet must belong to a branch
- Branch users can only view assigned branch wallets
- Owner can view all branch wallets

---

## 3. WALLET LEDGER

Create wallet ledger for all wallet movements.

Ledger movement types:

- Beginning Balance
- Wallet Funding
- Load Deduction
- Adjustment
- Reversal
- Correction

Ledger fields:

- wallet_id
- branch_id
- provider_id
- movement_type
- amount_in
- amount_out
- running_balance
- reference_type
- reference_id
- remarks
- performed_by
- created_at

Rules:

- Every wallet movement must create ledger entry
- Ledger must not be manually edited
- Corrections must use adjustment workflow
- Running balance must be accurate

---

## 4. WALLET FUNDING WORKFLOW

Build wallet funding workflow.

Process flow:

```text
Branch Requests Wallet Funding
→ Select Provider Wallet
→ Encode Funding Amount
→ Upload Proof if Available
→ Submit Request
→ Manager/Owner Reviews
→ Approve or Reject
→ If Approved, Wallet Balance Increases
→ Wallet Ledger Updated
→ Cash-Out/Fund Transfer Prepared
→ Audit Log Created
```

Funding fields:

- funding_number
- wallet_id
- branch_id
- provider_id
- amount
- funding_date
- payment_method
- reference_number nullable
- proof_file nullable
- status
- requested_by
- approved_by nullable
- approved_at nullable
- rejection_reason nullable
- remarks

Statuses:

- Draft
- Pending
- Approved
- Rejected
- Cancelled

Rules:

- Funding must require approval if configured
- Approved funding updates wallet balance
- Rejected funding does not update wallet balance
- Proof file must be validated
- Funding approval must be audit logged

---

## 5. LOAD SALES TRANSACTION

Build digital load sales transaction module.

Process flow:

```text
Customer Requests Load
→ Cashier Selects Provider
→ Cashier Selects Wallet
→ Cashier Enters Mobile Number
→ Cashier Enters Load Amount
→ System Validates Wallet Balance
→ System Checks Duplicate/Suspicious Transaction
→ Cashier Selects Payment Method
→ Cashier Confirms Transaction
→ Wallet Balance Deducted
→ Wallet Ledger Updated
→ Load Sale Recorded
→ Commission Computed
→ Cash-In Prepared/Created
→ Airtime Dashboard Updated
→ Audit Log Created
```

Transaction fields:

- transaction_number
- branch_id
- cashier_id
- provider_id
- wallet_id
- customer_mobile_number
- load_amount
- commission_amount
- total_amount
- payment_method_id
- payment_reference nullable
- transaction_status
- remarks
- processed_at

Transaction statuses:

- Successful
- Pending
- Failed
- Cancelled
- Reversed

Rules:

- Wallet must have sufficient balance
- Load amount must be greater than zero
- Wallet balance cannot become negative
- Mobile number must be validated
- Transaction must be branch-based
- Cashier can only create load transaction for assigned branch

---

## 6. COMMISSION TRACKING

Create commission computation and tracking.

Commission may be based on:

- Provider default commission
- Fixed amount
- Percentage
- Manual override if permitted

Commission fields:

- transaction_id
- provider_id
- branch_id
- commission_type
- commission_value
- commission_amount
- computed_by
- remarks

Rules:

- Commission must be recorded per transaction
- Manual override requires permission
- Commission must appear in reports

---

## 7. DUPLICATE AND SUSPICIOUS TRANSACTION DETECTION

Build validation for:

- Same mobile number
- Same amount
- Same provider
- Same branch
- Repeated transaction within configurable time window
- High-value load transaction
- Duplicate reference number
- Negative wallet attempt

If suspicious:

- Warn cashier
- Require confirmation or approval if configured
- Notify manager/owner
- Log event

---

## 8. FAILED / CANCELLED / REVERSED TRANSACTIONS

Support transaction correction.

Rules:

- Failed transaction should not deduct wallet if not completed
- Cancelled transaction should not deduct wallet if not completed
- Reversal must restore wallet balance if wallet was already deducted
- Reversal must create wallet ledger entry
- Reversal must require reason
- Reversal must require permission
- Reversal must be audit logged

---

## 9. WALLET ADJUSTMENT WORKFLOW

Build wallet adjustment workflow.

Adjustment reasons:

- Encoding correction
- Provider correction
- Failed transaction correction
- Reversal correction
- Beginning balance correction
- Other

Process:

```text
User Creates Wallet Adjustment Request
→ Select Wallet
→ Select Increase or Decrease
→ Encode Amount
→ Enter Reason
→ Submit for Approval
→ Approver Reviews
→ If Approved, Wallet Balance Updates
→ Wallet Ledger Updated
→ Audit Log Created
```

Statuses:

- Pending
- Approved
- Rejected
- Cancelled

Rules:

- Adjustment requires approval
- Reason is required
- Adjustment cannot cause negative balance unless authorized
- All adjustments must be logged

---

## 10. AIRTIME CASH FLOW INTEGRATION PREPARATION

Prepare integration with cash flow.

For each successful load sale:

- Prepare cash-in entry
- Link payment method
- Link cashier
- Link branch
- Link transaction reference

For wallet funding:

- Prepare cash-out or fund transfer entry
- Link funding reference
- Link branch
- Link proof

Do not duplicate entries if Phase 5 creates actual cash flow records later.

Use clean integration service preparation.

---

## 11. AIRTIME DASHBOARD

Create airtime dashboard.

Cards:

- Today’s load sales
- Monthly load sales
- Total commission
- Total wallet balance
- Low wallet count
- Pending funding requests
- Failed transactions
- Reversed transactions

Charts:

- Load sales per provider
- Load sales per branch
- Wallet balance per branch
- Commission trend
- Daily load sales trend

Tables:

- Recent load transactions
- Low wallet list
- Pending wallet funding
- Suspicious transactions

---

## 12. AIRTIME REPORTS

Create reports:

- Daily load sales
- Monthly load sales
- Load sales per branch
- Load sales per provider
- Load sales per cashier
- Wallet balance report
- Wallet funding report
- Wallet ledger report
- Commission report
- Failed/reversed transaction report

Reports must support:

- Date filter
- Branch filter
- Provider filter
- Cashier filter
- Status filter
- Excel export
- PDF export
- Print view

---

# DATABASE REQUIREMENTS

Create or update migrations:

- airtime_providers
- airtime_wallets
- airtime_wallet_ledgers
- airtime_wallet_fundings
- airtime_transactions
- airtime_commissions
- airtime_wallet_adjustments
- airtime_alerts

Relationships:

- Provider has many wallets
- Branch has many wallets
- Wallet belongs to provider
- Wallet belongs to branch
- Wallet has many ledgers
- Wallet has many fundings
- Wallet has many transactions
- Transaction belongs to provider
- Transaction belongs to wallet
- Transaction belongs to branch
- Transaction belongs to cashier/user
- Commission belongs to transaction
- Wallet adjustment belongs to wallet

---

# BACKEND REQUIREMENTS

Create controllers:

- AirtimeProviderController
- AirtimeWalletController
- AirtimeWalletLedgerController
- AirtimeFundingController
- AirtimeTransactionController
- AirtimeCommissionController
- AirtimeWalletAdjustmentController
- AirtimeDashboardController
- AirtimeReportController

Create services:

- AirtimeProviderService
- AirtimeWalletService
- WalletLedgerService
- AirtimeFundingService
- AirtimeTransactionService
- AirtimeCommissionService
- AirtimeValidationService
- AirtimeAlertService
- AirtimeCashFlowIntegrationService
- AirtimeReportService

Business logic must be inside services.

Controllers must remain clean.

---

# UI/UX REQUIREMENTS

Create responsive screens:

- Airtime dashboard
- Provider list
- Provider create/edit form
- Wallet list
- Wallet details
- Wallet ledger
- Wallet funding request form
- Wallet funding approval page
- Load transaction form
- Load transaction list
- Load transaction details
- Wallet adjustment page
- Airtime reports page

UI requirements:

- Clear wallet balance cards
- Provider badges/icons
- Status badges
- Branch filters
- Date filters
- Responsive tables
- Mobile-friendly forms
- Confirmation prompts before deduction
- Warning alerts for low wallet
- Warning alerts for suspicious transaction

---

# SECURITY REQUIREMENTS

Implement permissions:

- view_airtime_dashboard
- manage_airtime_providers
- view_airtime_wallets
- create_airtime_transaction
- view_airtime_transactions
- create_wallet_funding
- approve_wallet_funding
- create_wallet_adjustment
- approve_wallet_adjustment
- reverse_airtime_transaction
- view_airtime_reports
- export_airtime_reports

Rules:

- Branch users can only access assigned branch wallets
- Cashiers can only create transactions for assigned branch
- Owner can view all branch airtime data
- Wallet funding approval is restricted
- Wallet adjustment approval is restricted
- Reversal is restricted
- Manual commission override is restricted

---

# AUDIT TRAIL REQUIREMENTS

Log:

- Provider created/updated
- Wallet created/updated
- Wallet funding requested
- Wallet funding approved/rejected
- Load transaction created
- Wallet deducted
- Wallet adjusted
- Transaction reversed
- Transaction cancelled
- Commission computed/changed
- Report exported

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

- Low wallet balance
- Wallet funding request
- Wallet funding approved/rejected
- Suspicious load transaction
- Failed load transaction
- Reversed load transaction
- High-value load transaction
- Pending wallet adjustment

---

# VALIDATE SECTION

Validate:

## Provider
- Provider CRUD works
- Commission settings work
- Provider activation/deactivation works

## Wallet
- Wallet creation works
- Wallet uniqueness per branch/provider works
- Wallet balance is accurate
- Wallet ledger is accurate
- Negative balance is blocked

## Funding
- Funding request works
- Approval works
- Rejection works
- Approved funding updates wallet
- Rejected funding does not update wallet

## Transaction
- Load transaction works
- Wallet deduction works
- Duplicate detection works
- Suspicious transaction alert works
- Failed/cancelled/reversed handling works

## Commission
- Commission computation works
- Commission reports correctly

## Reports
- Filters work
- Export works
- Print view works

## Security
- Branch restriction works
- Permissions work
- Unauthorized access is blocked

## UI
- Airtime pages are responsive
- Tables do not overflow
- Forms are mobile-friendly

---

# FIX SECTION

If issues are found:

- Fix wallet balance computation
- Fix ledger running balance
- Fix duplicate transaction validation
- Fix commission computation
- Fix funding approval logic
- Fix reversal restoration
- Fix branch leakage
- Fix permission leaks
- Fix report inaccuracies
- Fix unresponsive airtime pages
- Refactor duplicated airtime logic

Revalidate after fixing.

---

# GATEWAY REVIEW SECTION

Before marking Phase 4 complete, verify:

- global_master.md is followed
- airtime providers are database-driven
- branch wallets are working
- wallet ledger is accurate
- wallet funding workflow works
- load transactions are working
- wallet deduction is accurate
- commission tracking works
- reversal handling works
- low wallet alerts work
- suspicious transaction detection works
- branch restrictions are enforced
- audit logs are complete
- no negative wallet balance
- no unauthorized wallet adjustment
- no unauthorized reversal
- no broken responsive UI
- no route conflict
- no migration conflict

---

# EXPECTED OUTPUT

At the end of Phase 4, provide:

- Complete airtime provider module
- Complete branch wallet management
- Complete wallet ledger
- Complete wallet funding workflow
- Complete load transaction module
- Complete commission tracking
- Complete suspicious transaction detection
- Complete failed/cancelled/reversed handling
- Complete wallet adjustment workflow
- Cash flow integration preparation
- Complete airtime dashboard
- Complete airtime reports
- Updated migrations
- Updated models
- Updated controllers
- Updated services
- Updated routes
- Updated views
- Updated permissions
- Updated audit logs
- Updated notifications
- Responsive airtime UI

PHASE 4 IS NOT COMPLETE UNTIL ALL VALIDATIONS PASS.
````
