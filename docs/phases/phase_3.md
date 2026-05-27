````markdown
# RC STORE RMS — PHASE 3
# POS & SALES MANAGEMENT
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
DO NOT IGNORE INVENTORY INTEGRATION.

---

# PHASE OBJECTIVE

Build the complete POS and Sales Management Module for RC Store RMS.

This phase must provide a fast, secure, accurate, touchscreen-friendly, barcode-supported, multi-branch Point-of-Sale system that records sales, validates stock availability, deducts inventory, handles payments, prints receipts, supports discounts, manages returns/exchanges, and updates sales monitoring in real time.

This module depends on:

- Phase 1 — User, Roles, Permissions & Branch Management
- Phase 2 — Product & Inventory Management

---

# BUILD SECTION

## 1. POS INTERFACE

Create a touchscreen-friendly POS screen.

The POS UI must include:

- Product search bar
- Barcode scan input
- Category filter
- Product grid
- Product quick buttons
- Cart panel
- Quantity controls
- Discount field
- Payment method section
- Customer selection optional
- Checkout button
- Hold transaction button
- Clear cart button
- Receipt preview button

UI rules:

- Large buttons
- Minimal clicks
- Fast search
- Touch-friendly controls
- Responsive layout
- Works on desktop, laptop, tablet, mobile, and POS touchscreen

---

## 2. SALES TRANSACTION WORKFLOW

Implement this complete flow:

```text
Cashier Logs In
→ System Validates Assigned Branch
→ Cashier Opens POS
→ Cashier Scans/Searches Product
→ System Checks Branch Inventory
→ Product Added to Cart
→ Quantity Validated
→ IMEI Selected if Required
→ Discount Applied if Authorized
→ Payment Method Selected
→ Payment Amount Entered
→ System Validates Payment
→ Sale Confirmed
→ Sales Record Created
→ Inventory Automatically Deducted
→ IMEI Status Updated if Applicable
→ Cash-In Record Prepared/Created
→ Receipt Generated
→ Audit Log Created
→ Dashboard Updated
```

---

## 3. SALES RECORD MANAGEMENT

Create complete sales records.

Sales fields:

- sales_number
- branch_id
- cashier_id
- customer_id nullable
- sales_date
- sales_time
- subtotal_amount
- discount_amount
- tax_amount nullable
- total_amount
- paid_amount
- change_amount
- payment_status
- sales_status
- remarks

Sales statuses:

- Completed
- Held
- Voided
- Refunded
- Partially Refunded
- Cancelled

Payment statuses:

- Paid
- Partial
- Unpaid
- Refunded
- Cancelled

---

## 4. SALES ITEM MANAGEMENT

Each sales item must include:

- sale_id
- product_id
- imei_id nullable
- quantity
- cost_price
- selling_price
- discount_amount
- subtotal
- item_status
- warranty_required
- warranty_status

Rules:

- Serialized/IMEI products must require IMEI selection
- Non-serialized products use quantity deduction
- Sale item cost price must be captured for profit reporting
- Sale item must link to inventory movement

---

## 5. PAYMENT MANAGEMENT

Support multiple payment methods:

- Cash
- GCash
- Maya
- Bank Transfer
- Card
- Split Payment

Payment fields:

- sale_id
- payment_method_id
- payment_reference nullable
- amount
- received_by
- received_at
- payment_status
- remarks

Rules:

- Cash payment must compute change
- E-wallet payment may require reference number
- Split payment must total the final amount
- Payment cannot be less than total unless partial payment is intentionally allowed
- Payment method must be database-driven

---

## 6. PAYMENT METHOD MANAGEMENT

Create payment method setup.

Fields:

- payment_method_name
- payment_type
- requires_reference
- status

Payment types:

- Cash
- E-Wallet
- Bank
- Card
- Other

Do not hardcode payment methods.

---

## 7. IMEI / SERIAL SALES HANDLING

For serialized products:

- Require IMEI selection before checkout
- Only show available IMEI from the selected branch
- Prevent sale of sold/reserved/defective/lost IMEI
- Update IMEI status to Sold after successful checkout
- Link IMEI to sale item
- Prepare warranty auto-registration for Phase 6

Rules:

- IMEI cannot be sold twice
- IMEI must belong to the selling branch
- IMEI must be available

---

## 8. INVENTORY DEDUCTION

After successful sale:

- Deduct product quantity from branch inventory
- Create inventory movement ledger entry
- Update quantity on hand
- Update quantity available
- Update IMEI status if applicable
- Prevent negative stock

Movement type:

- Sale Deduction

Rules:

- No stock deduction if sale fails
- No stock deduction if payment is invalid
- No negative inventory
- All inventory changes must be logged

---

## 9. DISCOUNT MANAGEMENT

Support discount application.

Discount types:

- Fixed amount discount
- Percentage discount
- Manual discount
- Promo discount preparation
- Senior/PWD discount preparation

Rules:

- Discount permission required
- Discount cannot exceed subtotal
- Discount reason required if configured
- Discount must be audit logged
- Discount must be reflected in sales report

---

## 10. HELD TRANSACTIONS

Build held transaction feature.

Use case:

- Customer temporarily pauses checkout
- Cashier holds cart
- Cashier resumes later

Rules:

- Held transaction must not deduct inventory
- Held transaction must not affect sales
- Held transaction can be resumed or cancelled
- Held transaction must belong to cashier and branch

---

## 11. VOID SALES WORKFLOW

Build void sales process.

Process flow:

```text
Cashier/Manager Selects Sale
→ Void Request Created
→ Reason Required
→ Manager/Owner Approval Required
→ If Approved, Sale Marked Voided
→ Inventory Restored
→ Cash/Payment Adjustment Recorded
→ Audit Log Created
```

Void rules:

- Completed sale cannot be deleted
- Voiding must require permission
- Voiding must require reason
- Voiding must restore inventory if applicable
- Voiding must restore IMEI status if applicable
- Voiding must be audit logged

Void statuses:

- Pending
- Approved
- Rejected
- Cancelled

---

## 12. SALES RETURN / EXCHANGE WORKFLOW

Build return and exchange workflow.

Process flow:

```text
Customer Requests Return/Exchange
→ Staff Searches Receipt
→ System Validates Sale
→ Select Item for Return/Exchange
→ Manager Approval Required
→ Item Condition Recorded
→ Refund or Exchange Processed
→ Inventory Updated
→ Cash Adjustment Recorded
→ Audit Log Created
```

Return statuses:

- Pending
- Approved
- Rejected
- Completed
- Cancelled

Return rules:

- Return quantity cannot exceed original quantity
- Return must link to original sale
- Serialized item return must restore IMEI status properly
- Refund must be recorded
- Exchange must create proper inventory movement

---

## 13. RECEIPT GENERATION

Create sales receipt.

Receipt must include:

- Store name
- Branch name
- Branch address
- Receipt number
- Transaction date/time
- Cashier name
- Product list
- IMEI/serial number if applicable
- Quantity
- Unit price
- Discount
- Subtotal
- Total
- Payment method
- Amount paid
- Change
- Warranty note
- Thank you message

Features:

- Print receipt
- Reprint receipt
- PDF receipt preparation
- QR receipt preparation

Rules:

- Reprint must be permission-controlled
- Reprint must be audit logged

---

## 14. SALES DASHBOARD

Create sales dashboard.

Cards:

- Today’s sales
- Monthly sales
- Total transactions
- Average transaction value
- Cash sales
- E-wallet sales
- Pending voids
- Pending returns

Charts:

- Sales trend
- Sales by branch
- Sales by cashier
- Sales by payment method
- Top selling products

---

## 15. SALES LIST AND DETAILS

Create sales management screens:

- Sales list
- Sales details
- Receipt view
- Payment details
- Sale item details
- Void request form
- Return request form
- Exchange form

Filters:

- Date range
- Branch
- Cashier
- Payment method
- Status
- Product

---

# DATABASE REQUIREMENTS

Create or update migrations:

- sales
- sale_items
- sale_payments
- payment_methods
- held_transactions
- held_transaction_items
- sale_void_requests
- sale_returns
- sale_return_items
- receipt_settings

Relationships:

- Sale belongs to branch
- Sale belongs to cashier/user
- Sale may belong to customer
- Sale has many sale items
- Sale has many payments
- Sale item belongs to product
- Sale item may belong to IMEI
- Sale payment belongs to sale
- Void request belongs to sale
- Return belongs to sale
- Held transaction belongs to cashier
- Held transaction belongs to branch

---

# BACKEND REQUIREMENTS

Create controllers:

- POSController
- SalesController
- SalePaymentController
- PaymentMethodController
- HeldTransactionController
- SaleVoidController
- SaleReturnController
- ReceiptController
- SalesDashboardController

Create services:

- POSService
- SalesService
- PaymentService
- DiscountService
- ReceiptService
- SaleVoidService
- SaleReturnService
- SalesInventoryService
- SalesAuditService

Business logic must be inside services.

Controllers must remain clean.

---

# UI/UX REQUIREMENTS

Create responsive screens:

- POS screen
- Sales dashboard
- Sales list
- Sales details
- Receipt view
- Payment method setup
- Held transactions list
- Void request page
- Return/exchange page

UI requirements:

- Touchscreen-friendly POS
- Large buttons
- Responsive cart panel
- Fast product lookup
- Clear payment computation
- Clear checkout confirmation
- Status badges
- Responsive tables
- Mobile-friendly forms

---

# SECURITY REQUIREMENTS

Implement permissions:

- view_pos
- create_sale
- view_sales
- apply_discount
- manage_payment_methods
- hold_transaction
- void_sale
- approve_void_sale
- create_sales_return
- approve_sales_return
- reprint_receipt
- view_sales_dashboard

Rules:

- Cashier can only create sales for assigned branch
- Branch manager can only manage assigned branch sales
- Owner can view all branch sales
- Discount requires permission
- Void requires approval
- Return requires approval
- Receipt reprint requires permission
- Branch sales must not leak across unauthorized users

---

# AUDIT TRAIL REQUIREMENTS

Log:

- Sale created
- Payment recorded
- Discount applied
- Receipt printed
- Receipt reprinted
- Held transaction created
- Held transaction resumed
- Held transaction cancelled
- Void requested
- Void approved/rejected
- Return requested
- Return approved/rejected
- Inventory deducted
- Inventory restored

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

- High-value sale
- Void request
- Return request
- Pending approval
- Failed checkout
- Inventory shortage during POS
- Cashier daily summary preparation

---

# VALIDATE SECTION

Validate:

## POS
- Product search works
- Barcode search works
- Product grid works
- Cart calculation works
- Quantity adjustment works
- Checkout works
- Held transaction works

## Payments
- Cash payment works
- Change computation works
- E-wallet reference validation works
- Split payment works
- Invalid payment is blocked

## Inventory
- Inventory deduction works
- Negative stock is blocked
- IMEI status updates
- Inventory ledger is created

## Discounts
- Discount computation works
- Unauthorized discount is blocked
- Discount audit log is created

## Returns / Voids
- Void request works
- Void approval works
- Inventory restoration works
- Return workflow works
- Exchange workflow works

## Receipt
- Receipt prints correctly
- Receipt reprint works
- Reprint is logged

## Security
- Branch restriction works
- Permissions work
- Unauthorized access is blocked

## UI
- POS is responsive
- POS works on touchscreen
- Sales pages work on mobile
- Tables do not overflow

---

# FIX SECTION

If issues are found:

- Fix cart calculation
- Fix payment computation
- Fix split payment errors
- Fix inventory deduction
- Fix IMEI validation
- Fix receipt layout
- Fix discount permission
- Fix void workflow
- Fix return workflow
- Fix branch leakage
- Fix permission leaks
- Fix responsive POS layout
- Refactor duplicated sales logic

Revalidate after fixing.

---

# GATEWAY REVIEW SECTION

Before marking Phase 3 complete, verify:

- global_master.md is followed
- POS works correctly
- Sales records are accurate
- Inventory deduction is accurate
- IMEI sale handling works
- Payments are accurate
- Receipts are working
- Discounts are permission-controlled
- Void workflow is controlled
- Return workflow is controlled
- Audit logs are complete
- Branch restrictions are enforced
- No duplicate IMEI sale
- No negative stock
- No unauthorized discounts
- No unauthorized void/refund
- No broken responsive UI
- No route conflict
- No migration conflict

---

# EXPECTED OUTPUT

At the end of Phase 3, provide:

- Complete POS module
- Complete sales management
- Complete payment management
- Complete discount handling
- Complete held transaction feature
- Complete receipt generation
- Complete void workflow
- Complete return/exchange workflow
- Complete inventory deduction integration
- Complete IMEI sales handling
- Complete sales dashboard
- Updated migrations
- Updated models
- Updated controllers
- Updated services
- Updated routes
- Updated views
- Updated permissions
- Updated audit logs
- Updated notifications
- Responsive POS and sales UI

PHASE 3 IS NOT COMPLETE UNTIL ALL VALIDATIONS PASS.
````
