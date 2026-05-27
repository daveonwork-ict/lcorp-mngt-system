````markdown
# RC STORE RMS — PHASE 15
# INSTALLMENT / RESERVATION MANAGEMENT
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
DO NOT IGNORE CUSTOMER, INVENTORY, POS, AND CASH FLOW INTEGRATION.

---

# PHASE OBJECTIVE

Build the complete Installment and Reservation Management Module for RC Store RMS.

This optional phase must allow RC Store to manage:

- Product reservations
- Reserved item locking
- Reservation fees
- Reservation expiration
- Installment accounts
- Down payments
- Payment schedules
- Partial payments
- Overdue accounts
- Customer receivables
- Penalties
- Item release conditions
- Reservation and installment reports

This phase depends on:

- Phase 1 — User, Roles, Permissions & Branch Management
- Phase 2 — Product & Inventory Management
- Phase 3 — POS & Sales Management
- Phase 5 — Cash Flow, Expenses & Daily Closing
- Phase 6 — Warranty & Customer Management

---

# BUILD SECTION

## 1. RESERVATION MANAGEMENT

Create product reservation workflow.

Process flow:

```text
Customer Requests Reservation
→ Staff Searches or Creates Customer
→ Staff Selects Product / IMEI
→ System Validates Availability
→ Reservation Fee Recorded
→ Expiration Date Set
→ Item Marked as Reserved
→ Customer Completes Payment or Reservation Expires
```

Reservation fields:

- reservation_number
- branch_id
- customer_id
- product_id
- imei_id nullable
- reservation_date
- reservation_fee
- total_price
- balance_amount
- expiration_date
- reservation_status
- created_by
- remarks

Reservation statuses:

- Pending
- Active
- Completed
- Expired
- Cancelled
- Refunded

Rules:

- Reserved item cannot be sold to another customer
- Serialized item must lock selected IMEI
- Reservation expiration must be monitored
- Reservation cancellation must release reserved item
- Reservation fee must integrate with cash-in
- All reservation actions must be audit logged

---

## 2. RESERVATION PAYMENT TRACKING

Create reservation payment tracking.

Payment fields:

- reservation_id
- payment_method_id
- payment_reference nullable
- amount_paid
- received_by
- received_at
- remarks

Rules:

- Payment must reduce reservation balance
- Payment must create cash-in/ledger entry
- Payment cannot exceed balance unless authorized
- Payment must be audit logged

---

## 3. RESERVATION COMPLETION

Create reservation completion process.

Process flow:

```text
Customer Pays Remaining Balance
→ System Validates Full Payment
→ Reservation Marked Completed
→ POS Sale Record Created or Linked
→ Inventory Deducted
→ IMEI Marked Sold if Applicable
→ Warranty Prepared if Applicable
```

Rules:

- Reservation cannot complete unless fully paid or authorized
- Completion must update inventory
- Completion must prevent duplicate sale
- Completion must link to customer and product
- Completion must trigger warranty readiness

---

## 4. RESERVATION EXPIRATION

Create reservation expiration monitoring.

Rules:

- Expired reservations must release reserved stock
- Expired IMEI must return to Available status
- Expired reservation must notify branch/manager
- Refund policy must be configurable
- Expiration must be audit logged

---

## 5. INSTALLMENT ACCOUNT MANAGEMENT

Create installment account workflow.

Process flow:

```text
Customer Purchases Item via Installment
→ Staff Selects Customer
→ Staff Selects Product / IMEI
→ System Validates Item Availability
→ Down Payment Recorded
→ Payment Terms Defined
→ Payment Schedule Generated
→ Installment Account Created
→ Item Reserved or Released Based on Policy
→ Customer Makes Scheduled Payments
→ Balance Reduces
→ Account Completed When Fully Paid
```

Installment fields:

- installment_number
- branch_id
- customer_id
- sale_id nullable
- product_id
- imei_id nullable
- total_amount
- downpayment_amount
- balance_amount
- payment_terms
- payment_frequency
- start_date
- due_date
- installment_status
- created_by
- approved_by nullable
- remarks

Installment statuses:

- Pending
- Active
- Completed
- Overdue
- Defaulted
- Cancelled

Rules:

- Down payment must be recorded
- Balance must compute accurately
- Serialized item must lock IMEI
- Payment terms must be configurable
- Installment approval may be required
- Installment must be audit logged

---

## 6. PAYMENT SCHEDULE GENERATION

Create payment schedule system.

Supported schedules:

- Weekly
- Semi-monthly
- Monthly
- Custom

Schedule fields:

- installment_id
- schedule_number
- due_date
- amount_due
- amount_paid
- balance_due
- penalty_amount nullable
- payment_status
- remarks

Payment statuses:

- Unpaid
- Partial
- Paid
- Overdue
- Waived

Rules:

- Schedule must total installment balance
- Payment schedule must be generated from selected terms
- Overdue status must be detected
- Partial payments must be supported

---

## 7. INSTALLMENT PAYMENT RECORDING

Create installment payment recording.

Payment fields:

- payment_number
- installment_id
- schedule_id nullable
- branch_id
- customer_id
- payment_date
- amount_paid
- payment_method_id
- reference_number nullable
- received_by
- remarks

Rules:

- Payment amount must be greater than zero
- Payment must reduce balance
- Payment must update payment schedule
- Payment must update receivable ledger
- Payment must create cash-in/financial ledger entry
- Payment must be audit logged

---

## 8. OVERDUE MONITORING

Create overdue monitoring.

System must detect:

- Missed due date
- Partial payment
- Overdue schedule
- Delinquent account
- Defaulted account

Features:

- Overdue list
- Overdue alerts
- Customer balance view
- Due date reminder readiness
- Aging report

---

## 9. PENALTY MANAGEMENT

Create optional penalty rules.

Penalty types:

- Fixed amount
- Percentage
- Daily penalty
- Grace period-based penalty

Penalty fields:

- installment_id
- schedule_id
- penalty_type
- penalty_amount
- reason
- status
- applied_by

Rules:

- Penalty rules must be configurable
- Penalty waiver requires permission
- Penalty must be audit logged

---

## 10. RECEIVABLE LEDGER

Create customer receivable ledger.

Ledger types:

- Installment Created
- Down Payment
- Installment Payment
- Penalty
- Penalty Waiver
- Adjustment
- Completion
- Cancellation

Fields:

- customer_id
- installment_id nullable
- reservation_id nullable
- ledger_type
- amount_debit
- amount_credit
- running_balance
- reference_type
- reference_id
- remarks
- created_by
- created_at

Rules:

- Ledger must track customer obligations
- Ledger must not be edited directly
- Corrections must use adjustment workflow
- Running balance must be accurate

---

## 11. ITEM RELEASE RULES

Create item release policy.

Possible release policies:

- Release only after full payment
- Release after down payment
- Release after approval
- Release based on owner configuration

Rules:

- Release policy must be configurable
- Item release must be permission-controlled
- Released installment item must still track balance if unpaid
- IMEI must be linked to customer
- Release must be audit logged

---

## 12. INSTALLMENT / RESERVATION DASHBOARD

Create dashboard.

Cards:

- Active reservations
- Expiring reservations
- Completed reservations
- Active installment accounts
- Overdue accounts
- Total receivables
- Payments collected today
- Defaulted accounts

Charts:

- Receivables trend
- Overdue accounts by branch
- Payment collection trend
- Reservation status chart

Tables:

- Expiring reservations
- Overdue accounts
- Recent payments
- Active installment accounts

---

## 13. INSTALLMENT / RESERVATION REPORTS

Create reports:

- Reservation report
- Expired reservation report
- Reservation payment report
- Installment account report
- Payment schedule report
- Payment collection report
- Overdue accounts report
- Receivables report
- Customer balance report
- Penalty report

Reports must support:

- Date filter
- Branch filter
- Customer filter
- Status filter
- Export to Excel
- Export to PDF
- Print view

---

# DATABASE REQUIREMENTS

Create or update migrations:

- reservations
- reservation_payments
- installment_accounts
- installment_schedules
- installment_payments
- installment_penalties
- receivable_ledgers
- item_release_logs

Relationships:

- Reservation belongs to branch
- Reservation belongs to customer
- Reservation belongs to product
- Reservation may belong to IMEI
- Reservation has many payments
- Installment belongs to branch
- Installment belongs to customer
- Installment belongs to product
- Installment may belong to IMEI
- Installment has many schedules
- Installment has many payments
- Installment has many penalties
- Customer has many receivable ledger entries

---

# BACKEND REQUIREMENTS

Create controllers:

- ReservationController
- ReservationPaymentController
- ReservationExpirationController
- InstallmentAccountController
- InstallmentScheduleController
- InstallmentPaymentController
- InstallmentPenaltyController
- ReceivableLedgerController
- ItemReleaseController
- InstallmentDashboardController
- InstallmentReportController

Create services:

- ReservationService
- ReservationPaymentService
- ReservationExpirationService
- InstallmentService
- PaymentScheduleService
- InstallmentPaymentService
- PenaltyService
- ReceivableLedgerService
- ItemReleaseService
- InstallmentInventoryIntegrationService
- InstallmentFinanceIntegrationService

Business logic must be inside services.

Controllers must remain clean.

---

# UI/UX REQUIREMENTS

Create responsive screens:

- Reservation dashboard
- Reservation list
- Reservation form
- Reservation details
- Reservation payment page
- Expiring reservation page
- Installment dashboard
- Installment account form
- Installment account details
- Payment schedule page
- Payment recording page
- Overdue accounts page
- Penalty management page
- Receivable ledger page
- Item release page
- Reports page

UI requirements:

- Status badges
- Customer summary cards
- Product/IMEI summary cards
- Payment schedule table
- Balance summary card
- Overdue warning indicators
- Responsive tables
- Mobile-friendly payment forms
- Clear action buttons

---

# SECURITY REQUIREMENTS

Implement permissions:

- view_reservations
- create_reservation
- update_reservation
- cancel_reservation
- refund_reservation
- record_reservation_payment
- view_installments
- create_installment
- approve_installment
- record_installment_payment
- manage_installment_penalty
- waive_installment_penalty
- release_installment_item
- view_receivable_ledger
- view_receivable_reports
- export_receivable_reports

Rules:

- Branch users can only access assigned branch records
- Owner can view all branches
- Payment posting is restricted
- Cancellation/refund is restricted
- Penalty waiver is restricted
- Item release is restricted
- Customer receivable data must be protected

---

# AUDIT TRAIL REQUIREMENTS

Log:

- Reservation created
- Reservation payment recorded
- Reservation completed
- Reservation expired
- Reservation cancelled
- Installment account created
- Installment approved
- Payment schedule generated
- Installment payment recorded
- Penalty applied
- Penalty waived
- Item released
- Receivable ledger posted
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

- Reservation expiring soon
- Reservation expired
- Installment payment due
- Installment payment overdue
- Payment received
- Account completed
- Account defaulted
- Item release pending
- Penalty applied

---

# VALIDATE SECTION

Validate:

## Reservation
- Reservation creation works
- Product availability validation works
- IMEI locking works
- Reservation payment works
- Expiration releases stock
- Completion creates/links sale correctly

## Installment
- Installment account creation works
- Down payment works
- Payment schedule generation works
- Payment posting works
- Balance computation works
- Overdue detection works
- Penalty application works
- Item release rules work

## Ledger
- Receivable ledger posts correctly
- Running balance is accurate
- Corrections do not edit ledger directly

## Reports
- Filters work
- Export works
- Print view works

## Security
- Branch restrictions work
- Permissions work
- Customer receivables are protected
- Unauthorized payment posting is blocked

## UI
- Reservation pages are responsive
- Installment pages are responsive
- Tables do not overflow
- Forms are mobile-friendly

---

# FIX SECTION

If issues are found:

- Fix reservation locking
- Fix reservation expiration
- Fix reservation payment
- Fix installment balance computation
- Fix schedule generation
- Fix payment posting
- Fix overdue detection
- Fix penalty logic
- Fix item release policy
- Fix receivable ledger running balance
- Fix branch leakage
- Fix permission leaks
- Fix report inaccuracies
- Fix responsive issues
- Refactor duplicated installment logic

Revalidate after fixing.

---

# GATEWAY REVIEW SECTION

Before marking Phase 15 complete, verify:

- global_master.md is followed
- reservation workflow works
- item reservation locking works
- reservation payment works
- reservation expiration works
- installment account workflow works
- payment schedule works
- installment payment works
- overdue monitoring works
- penalty management works
- receivable ledger works
- item release rules work
- reports work
- branch restrictions are enforced
- audit logs are complete
- no reserved item double-selling
- no incorrect balance
- no unauthorized payment posting
- no unauthorized item release
- no branch data leakage
- no broken responsive UI
- no route conflict
- no migration conflict

---

# EXPECTED OUTPUT

At the end of Phase 15, provide:

- Complete reservation management module
- Complete reservation payment tracking
- Complete reservation expiration handling
- Complete reservation completion workflow
- Complete installment account module
- Complete payment schedule generation
- Complete installment payment recording
- Complete overdue monitoring
- Complete penalty management
- Complete receivable ledger
- Complete item release rules
- Complete installment/reservation dashboard
- Complete installment/reservation reports
- Updated migrations
- Updated models
- Updated controllers
- Updated services
- Updated routes
- Updated views
- Updated permissions
- Updated audit logs
- Updated notifications
- Responsive reservation/installment UI

PHASE 15 IS NOT COMPLETE UNTIL ALL VALIDATIONS PASS.
````
