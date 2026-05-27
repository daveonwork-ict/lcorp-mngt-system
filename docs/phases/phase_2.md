# RC STORE RMS — PHASE 2
# PRODUCT & INVENTORY MANAGEMENT
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

---

# PHASE OBJECTIVE

Build the complete Product and Inventory Management Module for RC Store RMS.

This phase must centralize and standardize all product, stock, inventory movement, barcode, SKU, IMEI/serial tracking, stock-in, stock transfer, stock adjustment, physical inventory count, and low stock monitoring processes.

This module is critical because POS, warranty, reports, supplier purchasing, and executive analytics will depend on accurate inventory data.

---

# BUILD SECTION

## 1. PRODUCT CATEGORY MANAGEMENT

Create product category management.

Suggested categories:

- Mobile Phones
- Gadgets
- Accessories
- Beauty Products
- Digital Products
- SIM Cards
- Chargers
- Cables
- Office Supplies
- Other Retail Products

Features:

- Create category
- Edit category
- View category
- Activate/deactivate category
- Sort order
- Category description

Fields:

- category_code
- category_name
- description
- sort_order
- status

---

## 2. BRAND MANAGEMENT

Create brand management.

Features:

- Create brand
- Edit brand
- View brand
- Activate/deactivate brand

Fields:

- brand_code
- brand_name
- description
- status

---

## 3. PRODUCT MASTERLIST

Create complete product masterlist.

Product fields:

- product_code
- sku
- barcode
- product_name
- category_id
- brand_id
- model
- variant
- color
- description
- cost_price
- selling_price
- wholesale_price nullable
- reorder_level
- warranty_duration
- warranty_duration_type
- is_serialized
- is_imei_required
- status
- created_by
- updated_by

Rules:

- SKU must be unique
- Barcode must be unique if provided
- Product code must be unique
- Cost price must be numeric
- Selling price must be numeric
- Reorder level must be numeric
- Serialized products must require IMEI/serial tracking

---

## 4. PRODUCT PRICING HISTORY

Build product price history tracking.

Track:

- product_id
- old_cost_price
- new_cost_price
- old_selling_price
- new_selling_price
- changed_by
- changed_at
- remarks

All price changes must be audit logged.

---

## 5. BARCODE AND SKU SUPPORT

Build barcode/SKU support.

Features:

- Manual barcode entry
- Auto SKU generation
- Barcode uniqueness validation
- Barcode lookup
- Barcode scanning readiness
- Barcode label printing preparation

---

## 6. IMEI / SERIAL NUMBER TRACKING

Build IMEI/serial tracking for mobile phones and gadgets.

IMEI fields:

- product_id
- branch_id
- imei_number
- serial_number nullable
- status
- received_date
- sold_date nullable
- current_reference_type nullable
- current_reference_id nullable

IMEI statuses:

- Available
- Sold
- Reserved
- Transferred
- Defective
- Returned
- Under Warranty
- Lost

Rules:

- IMEI must be unique
- IMEI cannot be sold twice
- IMEI must be linked to branch inventory
- IMEI status must update during inventory movement
- IMEI must be available before sale

---

## 7. BRANCH INVENTORY MANAGEMENT

Create branch-based inventory.

Features:

- View inventory per branch
- View consolidated inventory
- Filter by branch
- Filter by category
- Filter by brand
- Filter by stock status
- View stock quantity
- View inventory value

Inventory fields:

- branch_id
- product_id
- quantity_on_hand
- quantity_reserved
- quantity_available
- average_cost
- inventory_value
- reorder_level
- updated_at

Rules:

- Branch users can only view assigned branch inventory
- Owner can view all branch inventory
- Quantity available = quantity on hand minus reserved quantity
- Negative stock must be prevented

---

## 8. STOCK-IN MANAGEMENT

Build stock-in workflow.

Process flow:

```text
Supplier Delivery
→ Inventory Staff Encodes Stock-In
→ Product and Quantity Validation
→ IMEI/Serial Validation if Required
→ Delivery Receipt Upload
→ Submit Stock-In
→ Approval if Required
→ Inventory Added to Branch
→ Inventory Movement Ledger Updated
→ Audit Log Created
```

Stock-in fields:

- stock_in_number
- branch_id
- supplier_id nullable
- received_date
- reference_number
- delivery_receipt_number
- remarks
- received_by
- status

Stock-in item fields:

- stock_in_id
- product_id
- quantity
- cost_price
- selling_price
- subtotal
- remarks

For serialized products:

- IMEI or serial numbers must be encoded per unit.

---

## 9. INVENTORY MOVEMENT LEDGER

Build complete inventory movement ledger.

Movement types:

- Stock-In
- Sale Deduction
- Stock Adjustment
- Stock Transfer Out
- Stock Transfer In
- Return
- Defective
- Lost
- Physical Count Adjustment

Ledger fields:

- branch_id
- product_id
- imei_id nullable
- movement_type
- quantity_in
- quantity_out
- running_balance
- reference_type
- reference_id
- remarks
- performed_by
- created_at

Rules:

- Every inventory movement must create a ledger entry
- Running balance must be accurate
- Ledger must be non-editable after posting
- Corrections must be done through adjustment, not direct editing

---

## 10. STOCK ADJUSTMENT MANAGEMENT

Build inventory adjustment workflow.

Adjustment reasons:

- Physical count discrepancy
- Damaged item
- Lost item
- Encoding correction
- Return to supplier
- Defective item
- Other

Process flow:

```text
Inventory Discrepancy Found
→ Staff Creates Adjustment Request
→ Manager Reviews Request
→ Approve or Reject
→ If Approved, Inventory Updates
→ Ledger Records Movement
→ Audit Log Saves Before/After
```

Statuses:

- Draft
- Pending
- Approved
- Rejected
- Cancelled

Rules:

- Approval required before inventory changes
- Reason required
- Negative stock not allowed unless authorized
- All adjustments must be audit logged

---

## 11. INVENTORY TRANSFER MANAGEMENT

Build inter-branch inventory transfer.

Process flow:

```text
Source Branch Creates Transfer Request
→ Select Destination Branch
→ Select Product and Quantity / IMEI
→ System Validates Availability
→ Manager / Owner Approval
→ Stock Marked In Transit
→ Receiving Branch Confirms Receipt
→ Source Branch Deducted
→ Destination Branch Added
→ Ledger Updated
```

Transfer statuses:

- Draft
- Pending Approval
- Approved
- In Transit
- Received
- Rejected
- Cancelled

Rules:

- Source and destination branch cannot be the same
- Source branch must have available stock
- Serialized items must select specific IMEI
- Receiving branch must confirm receipt
- Movement ledger must update both branches

---

## 12. PHYSICAL INVENTORY COUNT

Build physical count module.

Features:

- Create count session
- Select branch
- Select category or all products
- Encode counted quantity
- Encode counted IMEI if applicable
- Compare system quantity vs actual quantity
- Generate variance
- Create adjustment request from variance

Statuses:

- Open
- Submitted
- Reviewed
- Adjusted
- Cancelled

---

## 13. LOW STOCK ALERTS

Build inventory alerts.

Alert types:

- Low stock
- Out of stock
- Critical stock
- Negative stock attempt
- Duplicate IMEI attempt
- Pending transfer
- Pending adjustment

Alerts must appear in:

- Notification bell
- Inventory dashboard
- Executive dashboard

---

## 14. INVENTORY DASHBOARD

Create inventory dashboard.

Cards:

- Total products
- Total inventory value
- Low stock items
- Out-of-stock items
- Pending transfers
- Pending adjustments
- Serialized items
- Defective items

Charts:

- Inventory value per branch
- Product count per category
- Low stock by branch
- Inventory movement trend

---

# DATABASE REQUIREMENTS

Create or update migrations:

- product_categories
- brands
- products
- product_price_histories
- product_imeis
- branch_inventories
- stock_ins
- stock_in_items
- inventory_movements
- inventory_adjustments
- inventory_adjustment_items
- inventory_transfers
- inventory_transfer_items
- physical_counts
- physical_count_items
- inventory_alerts

Relationships:

- Category has many products
- Brand has many products
- Product belongs to category
- Product belongs to brand
- Product has many IMEI records
- Product has many branch inventories
- Branch has many inventories
- Stock-in has many items
- Transfer has many items
- Adjustment has many items
- Physical count has many items

---

# BACKEND REQUIREMENTS

Create controllers:

- ProductCategoryController
- BrandController
- ProductController
- ProductPriceHistoryController
- ProductImeiController
- BranchInventoryController
- StockInController
- InventoryMovementController
- InventoryAdjustmentController
- InventoryTransferController
- PhysicalCountController
- InventoryAlertController
- InventoryDashboardController

Create services:

- ProductService
- InventoryService
- StockInService
- InventoryMovementService
- InventoryAdjustmentService
- InventoryTransferService
- PhysicalCountService
- InventoryAlertService

Business logic must be inside services.

Controllers must remain clean.

---

# UI/UX REQUIREMENTS

Create responsive screens:

- Category list
- Category form
- Brand list
- Brand form
- Product masterlist
- Product create/edit form
- Product detail page
- IMEI list
- Branch inventory page
- Stock-in form
- Stock-in details
- Adjustment request form
- Transfer request form
- Transfer receiving page
- Physical count page
- Inventory ledger page
- Low stock alert page
- Inventory dashboard

UI requirements:

- Branch filter
- Category filter
- Brand filter
- Status badges
- Responsive tables
- Search fields
- Clear action buttons
- Mobile-friendly forms
- Touch-friendly controls

---

# SECURITY REQUIREMENTS

Implement permissions:

- view_inventory
- create_product
- edit_product
- deactivate_product
- view_stock_in
- create_stock_in
- approve_stock_in
- view_stock_adjustment
- create_stock_adjustment
- approve_stock_adjustment
- view_inventory_transfer
- create_inventory_transfer
- approve_inventory_transfer
- receive_inventory_transfer
- view_physical_count
- create_physical_count
- view_inventory_reports

Rules:

- Branch users can only access assigned branch inventory
- Owner can access all branches
- Unauthorized users cannot adjust stock
- Unauthorized users cannot approve stock changes
- IMEI records must not be exposed to unauthorized users

---

# AUDIT TRAIL REQUIREMENTS

Log:

- Category created/updated
- Brand created/updated
- Product created/updated
- Price changed
- IMEI added/updated
- Stock-in created/approved
- Inventory adjusted
- Transfer requested/approved/received
- Physical count submitted
- Low stock alert generated

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

- Low stock
- Out of stock
- Pending stock adjustment
- Pending transfer approval
- Transfer awaiting receipt
- Duplicate IMEI attempt
- Critical inventory issue

---

# VALIDATE SECTION

Validate:

## Product
- Product CRUD works
- SKU unique validation works
- Barcode unique validation works
- Price history works
- Category/brand linking works

## IMEI
- IMEI encoding works
- Duplicate IMEI is blocked
- IMEI status updates correctly
- Serialized products require IMEI

## Inventory
- Stock-in updates inventory
- Ledger is created
- Running balance is accurate
- Branch inventory is accurate
- Negative stock is blocked

## Transfer
- Transfer request works
- Transfer approval works
- Receiving works
- Both branch ledgers update correctly

## Adjustment
- Adjustment request works
- Approval works
- Inventory updates correctly
- Audit log records before/after

## Physical Count
- Count session works
- Variance is computed
- Adjustment request can be generated

## Security
- Branch restrictions work
- Permission restrictions work
- Unauthorized access is blocked

## UI
- All inventory pages are responsive
- Tables do not overflow
- Forms are mobile-friendly

---

# FIX SECTION

If issues are found:

- Fix product validation
- Fix SKU/barcode duplicate handling
- Fix IMEI duplicate handling
- Fix inventory balance computation
- Fix running balance errors
- Fix transfer workflow
- Fix adjustment workflow
- Fix physical count variance
- Fix permission leaks
- Fix branch data leakage
- Fix unresponsive inventory pages
- Refactor duplicated inventory logic

Revalidate after fixing.

---

# GATEWAY REVIEW SECTION

Before marking Phase 2 complete, verify:

- global_master.md is followed
- product masterlist is complete
- category and brand management are complete
- barcode/SKU support is working
- IMEI tracking is working
- stock-in is working
- inventory ledger is accurate
- adjustment workflow is working
- transfer workflow is working
- physical count is working
- low stock alerts are working
- branch restrictions are enforced
- audit logs are complete
- no hardcoded statuses
- no hardcoded branch IDs
- no negative stock
- no duplicate IMEI
- no broken responsive UI
- no route conflict
- no migration conflict

---

# EXPECTED OUTPUT

At the end of Phase 2, provide:

- Complete product category module
- Complete brand module
- Complete product masterlist
- Complete barcode/SKU support
- Complete IMEI/serial tracking
- Complete branch inventory module
- Complete stock-in workflow
- Complete inventory movement ledger
- Complete stock adjustment workflow
- Complete inventory transfer workflow
- Complete physical count module
- Complete low stock alerts
- Complete inventory dashboard
- Updated migrations
- Updated models
- Updated controllers
- Updated services
- Updated routes
- Updated views
- Updated permissions
- Updated audit logs
- Updated notifications
- Responsive inventory UI

PHASE 2 IS NOT COMPLETE UNTIL ALL VALIDATIONS PASS.