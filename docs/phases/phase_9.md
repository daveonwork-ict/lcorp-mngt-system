````markdown
# RC STORE RMS — PHASE 9
# SUPPLIER, PURCHASING & OFFICE SUPPLIES MANAGEMENT
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
DO NOT IGNORE INVENTORY AND FINANCE INTEGRATION.

---

# PHASE OBJECTIVE

Build the complete Supplier, Purchasing, Receiving, Supplier Payables, and Office Supplies Management Module for RC Store RMS.

This phase must centralize:

- Supplier records
- Purchase requests
- Purchase orders
- Receiving reports
- Supplier payables
- Supplier payments
- Office supplies monitoring
- Supply issuance
- Staff accountability
- Reorder monitoring

This module depends on:

- Phase 1 — User, Roles, Permissions & Branch Management
- Phase 2 — Product & Inventory Management
- Phase 5 — Cash Flow, Expenses & Daily Closing

---

# BUILD SECTION

## 1. SUPPLIER MANAGEMENT

Create supplier master management.

Supplier fields:

- supplier_code
- supplier_name
- contact_person
- contact_number
- email
- address
- product_categories
- payment_terms
- status
- remarks

Features:

- Create supplier
- Edit supplier
- View supplier
- Deactivate supplier
- View supplier transactions
- View supplier payables
- View supplier payment history
- View supplier performance

---

## 2. PURCHASE REQUEST MANAGEMENT

Create purchase request workflow.

Process flow:

```text
Low Stock / Branch Need Identified
→ Staff Creates Purchase Request
→ Manager Reviews Request
→ Owner/Admin Approves or Rejects
→ Approved Request Converts to Purchase Order
```

Purchase request fields:

- request_number
- branch_id
- requested_by
- request_date
- purpose
- priority
- status
- remarks

Purchase request item fields:

- purchase_request_id
- product_id
- requested_quantity
- estimated_cost nullable
- remarks

Statuses:

- Draft
- Pending
- Approved
- Rejected
- Cancelled
- Converted to PO

Rules:

- Product must exist
- Quantity must be greater than zero
- Rejection requires reason
- Approved request may be converted to PO
- All request actions must be audit logged

---

## 3. PURCHASE ORDER MANAGEMENT

Create purchase order workflow.

Process flow:

```text
Approved Purchase Request
→ Select Supplier
→ Generate Purchase Order
→ Encode Items, Quantity, and Cost
→ Submit PO
→ Approve PO if Required
→ Send/Mark as Sent
→ Supplier Delivery
→ Receiving Process
```

Purchase order fields:

- po_number
- supplier_id
- branch_id
- request_id nullable
- po_date
- expected_delivery_date
- total_amount
- status
- prepared_by
- approved_by nullable
- remarks

Purchase order item fields:

- purchase_order_id
- product_id
- quantity_ordered
- quantity_received
- unit_cost
- subtotal
- remarks

Statuses:

- Draft
- Pending
- Approved
- Sent
- Partially Received
- Fully Received
- Cancelled

Rules:

- PO items must be valid products
- Quantity ordered must be greater than zero
- Unit cost must be non-negative
- Total must be computed accurately
- PO approval must be permission-controlled
- PO cannot be fully received unless all quantities are received

---

## 4. RECEIVING REPORT MANAGEMENT

Create receiving report workflow.

Process flow:

```text
Supplier Delivery Arrives
→ Staff Opens Related PO
→ Validates Delivered Items
→ Encodes Quantity Received
→ Encodes IMEI/Serial if Required
→ Uploads Delivery Receipt / Invoice
→ Submits Receiving Report
→ Inventory Stock-In Generated
→ Supplier Payable Created/Updated
→ Audit Log Created
```

Receiving report fields:

- receiving_number
- purchase_order_id
- supplier_id
- branch_id
- received_date
- delivery_receipt_number
- invoice_number
- received_by
- status
- remarks

Receiving report item fields:

- receiving_report_id
- product_id
- quantity_received
- unit_cost
- subtotal
- remarks

Statuses:

- Draft
- Submitted
- Validated
- Cancelled

Rules:

- Quantity received cannot exceed quantity ordered
- Partial receiving must be supported
- Serialized products must require IMEI/serial entry
- Receiving must update purchase order received quantity
- Receiving must generate stock-in or inventory movement
- Receiving must generate supplier payable if applicable

---

## 5. PARTIAL RECEIVING

Support partial delivery.

Rules:

- Quantity received cannot exceed remaining quantity
- PO status must update to Partially Received
- Remaining quantity must be visible
- Fully received status only when all quantities are complete
- Partial receiving must be audit logged

---

## 6. SUPPLIER PAYABLES

Create supplier payable monitoring.

Payable fields:

- payable_number
- supplier_id
- branch_id
- receiving_report_id nullable
- invoice_number
- payable_date
- due_date nullable
- total_amount
- amount_paid
- balance_amount
- payment_status
- status
- remarks

Payment statuses:

- Unpaid
- Partial
- Paid
- Overdue
- Cancelled

Rules:

- Payable may be generated from receiving report
- Amount paid cannot exceed balance unless authorized
- Balance must compute accurately
- Payables must support aging reports

---

## 7. SUPPLIER PAYMENT RECORDING

Create supplier payment recording.

Payment fields:

- payment_number
- supplier_id
- payable_id
- branch_id
- payment_date
- payment_method_id
- reference_number nullable
- amount_paid
- proof_file nullable
- remarks
- paid_by

Rules:

- Payment amount must be greater than zero
- Payment amount cannot exceed payable balance unless authorized
- Payment must update payable status
- Supplier payment may create cash-out or financial ledger entry
- Proof file must be securely stored
- Payment must be audit logged

---

## 8. OFFICE SUPPLY CATEGORY MANAGEMENT

Create office supply category setup.

Fields:

- category_code
- category_name
- description
- status

Features:

- Create category
- Edit category
- Activate/deactivate category

---

## 9. OFFICE SUPPLIES MASTERLIST

Create office supplies masterlist.

Fields:

- supply_code
- supply_name
- category_id
- unit
- reorder_level
- description
- status

Features:

- Create supply
- Edit supply
- View supply
- Activate/deactivate supply

---

## 10. OFFICE SUPPLY INVENTORY

Create office supply branch inventory.

Fields:

- branch_id
- office_supply_id
- quantity_on_hand
- quantity_available
- reorder_level
- updated_at

Features:

- Stock-in supplies
- Adjust supplies
- Monitor remaining balance
- Low supply alerts
- View inventory per branch

---

## 11. SUPPLY ISSUANCE MANAGEMENT

Create supply issuance workflow.

Process flow:

```text
Staff Requests Supply
→ Manager Reviews Request
→ Supply Approved
→ Supply Issued
→ Staff Accountability Recorded
→ Supply Inventory Deducted
→ Audit Log Created
```

Issuance fields:

- issuance_number
- branch_id
- requested_by
- issued_to
- issued_by
- issue_date
- purpose
- status
- remarks

Issuance item fields:

- issuance_id
- office_supply_id
- quantity_requested
- quantity_issued
- remarks

Statuses:

- Pending
- Approved
- Issued
- Rejected
- Cancelled

Rules:

- Issuance cannot exceed available balance
- Approval required if configured
- Issuance must deduct office supply inventory
- Issuance must record staff accountability
- Issuance must be audit logged

---

## 12. STAFF ACCOUNTABILITY

Create staff accountability monitoring.

Track:

- Employee
- Branch
- Supply item
- Quantity issued
- Date issued
- Issued by
- Received by
- Purpose
- Remarks

Features:

- View accountability per staff
- View accountability per branch
- Export accountability report

---

## 13. REORDER ALERTS

Generate alerts for:

- Low retail stock
- Low office supplies
- Pending purchase request
- PO pending approval
- PO partially received
- Supplier payable due
- Supplier payable overdue
- Supply issuance request

Alerts must appear in:

- Notification bell
- Purchasing dashboard
- Executive dashboard

---

## 14. PURCHASING DASHBOARD

Create purchasing dashboard.

Cards:

- Active suppliers
- Pending purchase requests
- Pending purchase orders
- Partially received POs
- Supplier payables
- Overdue payables
- Low office supplies
- Pending supply issuance

Charts:

- Purchases per supplier
- Supplier payable aging
- Office supply usage
- Purchase trend

Tables:

- Recent purchase requests
- Pending POs
- Recent receiving reports
- Supplier payables
- Low office supplies

---

## 15. SUPPLIER & PURCHASING REPORTS

Create reports:

- Supplier list report
- Purchase request report
- Purchase order report
- Receiving report
- Supplier payable report
- Supplier aging report
- Supplier payment report
- Office supplies inventory report
- Supply issuance report
- Staff accountability report

Reports must support:

- Date filter
- Branch filter
- Supplier filter
- Status filter
- Product filter
- Export to Excel
- Export to PDF
- Print view

---

# DATABASE REQUIREMENTS

Create or update migrations:

- suppliers
- purchase_requests
- purchase_request_items
- purchase_orders
- purchase_order_items
- receiving_reports
- receiving_report_items
- supplier_payables
- supplier_payments
- office_supply_categories
- office_supplies
- office_supply_inventories
- office_supply_movements
- office_supply_issuances
- office_supply_issuance_items
- staff_accountabilities

Relationships:

- Supplier has many purchase orders
- Supplier has many receiving reports
- Supplier has many payables
- Purchase request belongs to branch
- Purchase request has many items
- Purchase order belongs to supplier
- Purchase order belongs to branch
- Purchase order has many items
- Receiving report belongs to purchase order
- Receiving report has many items
- Receiving report generates stock-in/inventory movement
- Supplier payable belongs to supplier
- Supplier payment belongs to supplier payable
- Office supply belongs to category
- Office supply has branch inventory
- Office supply issuance belongs to branch
- Office supply issuance has many items
- Staff accountability belongs to user/employee

---

# BACKEND REQUIREMENTS

Create controllers:

- SupplierController
- PurchaseRequestController
- PurchaseOrderController
- ReceivingReportController
- SupplierPayableController
- SupplierPaymentController
- OfficeSupplyCategoryController
- OfficeSupplyController
- OfficeSupplyInventoryController
- OfficeSupplyIssuanceController
- StaffAccountabilityController
- PurchasingDashboardController
- PurchasingReportController

Create services:

- SupplierService
- PurchaseRequestService
- PurchaseOrderService
- ReceivingReportService
- SupplierPayableService
- SupplierPaymentService
- OfficeSupplyService
- OfficeSupplyInventoryService
- OfficeSupplyIssuanceService
- StaffAccountabilityService
- PurchasingReportService
- PurchasingInventoryIntegrationService
- PurchasingFinanceIntegrationService

Business logic must be inside services.

Controllers must remain clean.

---

# UI/UX REQUIREMENTS

Create responsive screens:

- Supplier list
- Supplier create/edit form
- Supplier profile page
- Purchase request list
- Purchase request form
- Purchase order list
- Purchase order form
- Receiving report form
- Supplier payable page
- Supplier payment page
- Office supply category page
- Office supplies list
- Office supply inventory page
- Supply issuance page
- Staff accountability page
- Purchasing dashboard
- Purchasing reports page

UI requirements:

- Supplier profile summary cards
- PO status badges
- Payable status badges
- Branch filters
- Supplier filters
- Responsive tables
- Mobile-friendly forms
- Proof preview modal
- Approval buttons
- Warning indicators for overdue payables
- Low supply indicators

---

# SECURITY REQUIREMENTS

Implement permissions:

- view_suppliers
- manage_suppliers
- create_purchase_request
- approve_purchase_request
- create_purchase_order
- approve_purchase_order
- receive_purchase_order
- view_supplier_payables
- record_supplier_payment
- manage_office_supplies
- view_office_supply_inventory
- create_supply_issuance
- approve_supply_issuance
- view_staff_accountability
- view_purchasing_reports
- export_purchasing_reports

Rules:

- Branch users can only access assigned branch purchasing records
- Owner can view all branches
- Supplier payment recording is restricted
- PO approval is restricted
- Purchase request approval is restricted
- Supply issuance approval is restricted
- Proof files are access-controlled
- Supplier financial information must be restricted

---

# AUDIT TRAIL REQUIREMENTS

Log:

- Supplier created/updated
- Purchase request created
- Purchase request approved/rejected
- Purchase order created
- Purchase order approved/sent
- Receiving report submitted
- Stock-in generated from receiving
- Supplier payable created
- Supplier payment recorded
- Office supply created/updated
- Office supply issued
- Staff accountability recorded
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

- Purchase request submitted
- Purchase request approved/rejected
- PO pending approval
- PO partially received
- Supplier payable due
- Supplier payable overdue
- Office supply low stock
- Supply issuance request
- Supply issuance approved/rejected

---

# VALIDATE SECTION

Validate:

## Supplier
- Supplier CRUD works
- Supplier profile works
- Supplier transactions display correctly

## Purchasing
- Purchase request works
- Approval works
- PO creation works
- PO approval works
- PO status updates correctly

## Receiving
- Receiving report works
- Partial receiving works
- Quantity received cannot exceed ordered
- Stock-in/inventory movement is generated
- IMEI entry works for serialized products

## Payables
- Payable generation works
- Payment recording works
- Balance computation is accurate
- Aging report works

## Office Supplies
- Supply CRUD works
- Branch supply inventory works
- Supply issuance works
- Staff accountability works
- Low supply alerts work

## Reports
- Filters work
- Export works
- Print view works

## Security
- Branch restrictions work
- Permissions work
- Proof files are protected
- Unauthorized access is blocked

## UI
- Purchasing pages are responsive
- Tables do not overflow
- Forms are mobile-friendly

---

# FIX SECTION

If issues are found:

- Fix supplier validation
- Fix purchase request workflow
- Fix PO status update
- Fix receiving quantity validation
- Fix inventory integration
- Fix IMEI receiving logic
- Fix payable computation
- Fix supplier payment balance
- Fix office supply deduction
- Fix staff accountability
- Fix branch leakage
- Fix permission leaks
- Fix proof upload security
- Fix report inaccuracies
- Fix unresponsive purchasing pages
- Refactor duplicated purchasing logic

Revalidate after fixing.

---

# GATEWAY REVIEW SECTION

Before marking Phase 9 complete, verify:

- global_master.md is followed
- supplier management is complete
- purchase request workflow works
- purchase order workflow works
- receiving report works
- partial receiving works
- inventory integration works
- supplier payables work
- supplier payments work
- office supplies module works
- supply issuance works
- staff accountability works
- reorder alerts work
- reports work
- branch restrictions are enforced
- audit logs are complete
- no over-receiving
- no incorrect payable balance
- no unauthorized supplier payment
- no insecure proof file access
- no broken responsive UI
- no route conflict
- no migration conflict

---

# EXPECTED OUTPUT

At the end of Phase 9, provide:

- Complete supplier management module
- Complete purchase request workflow
- Complete purchase order workflow
- Complete receiving report workflow
- Complete partial receiving
- Complete inventory integration
- Complete supplier payables
- Complete supplier payment recording
- Complete office supply category module
- Complete office supplies module
- Complete office supply inventory
- Complete supply issuance workflow
- Complete staff accountability monitoring
- Complete reorder alerts
- Complete purchasing dashboard
- Complete purchasing reports
- Updated migrations
- Updated models
- Updated controllers
- Updated services
- Updated routes
- Updated views
- Updated permissions
- Updated audit logs
- Updated notifications
- Responsive purchasing and office supplies UI

PHASE 9 IS NOT COMPLETE UNTIL ALL VALIDATIONS PASS.
````
