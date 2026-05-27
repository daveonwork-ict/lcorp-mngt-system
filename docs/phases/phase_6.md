````markdown
# RC STORE RMS — PHASE 6
# WARRANTY & CUSTOMER MANAGEMENT
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
DO NOT IGNORE POS, INVENTORY, AND IMEI INTEGRATION.

---

# PHASE OBJECTIVE

Build the complete Warranty and Customer Management Module for RC Store RMS.

This phase must digitize and centralize customer records, customer purchase history, IMEI/serial ownership tracking, warranty registration, warranty lookup, warranty claims, repair/replacement tracking, warranty expiration alerts, and customer after-sales service monitoring.

This module depends on:

- Phase 1 — User, Roles, Permissions & Branch Management
- Phase 2 — Product & Inventory Management
- Phase 3 — POS & Sales Management

---

# BUILD SECTION

## 1. CUSTOMER MANAGEMENT MODULE

Create complete customer management.

Customer fields:

- customer_code
- first_name
- middle_name
- last_name
- suffix
- full_name
- mobile_number
- email nullable
- address nullable
- birthdate nullable
- gender nullable
- customer_type
- status
- created_by
- updated_by

Customer types:

- Walk-in
- Regular
- VIP
- Corporate
- Online Customer

Customer statuses:

- Active
- Inactive
- Blocklisted

Features:

- Create customer
- Edit customer
- View customer
- Deactivate customer
- Search by name
- Search by mobile number
- Search by customer code
- View purchase history
- View warranty history
- View transaction history
- Add customer notes

Rules:

- Customer mobile number must be validated
- Duplicate customer detection must be implemented
- Customer records must be branch-aware through transactions
- Sensitive customer data must be permission-protected

---

## 2. CUSTOMER PROFILE PAGE

Create customer profile page.

Profile must show:

- Customer information
- Customer type
- Contact details
- Purchase history
- Warranty history
- Warranty claims
- Repair/replacement history
- Notes
- Total purchases
- Last purchase date

---

## 3. CUSTOMER PURCHASE HISTORY

Build customer purchase history.

Must display:

- Sales date
- Branch
- Receipt number
- Cashier
- Product purchased
- IMEI/serial number if applicable
- Quantity
- Amount paid
- Payment method
- Warranty status

Rules:

- Purchase history must pull from actual POS sales
- Do not duplicate sales data unnecessarily
- Respect branch access restrictions

---

## 4. WARRANTY RULE MANAGEMENT

Create warranty rule setup.

Warranty rule fields:

- rule_code
- rule_name
- product_category_id nullable
- brand_id nullable
- product_id nullable
- warranty_duration
- warranty_duration_type
- warranty_coverage
- exclusions
- requires_imei
- status

Warranty duration types:

- Days
- Months
- Years

Features:

- Create warranty rule
- Edit warranty rule
- View warranty rule
- Activate/deactivate rule
- Apply rule by category
- Apply rule by brand
- Apply rule by product

Rules:

- Product-level rule overrides brand/category rule if configured
- Warranty rules must be database-driven
- Do not hardcode warranty durations

---

## 5. WARRANTY AUTO-REGISTRATION

Automatically create warranty record after POS sale when applicable.

Process flow:

```text
Product Sold Through POS
→ System Checks Product Warranty Rule
→ System Checks Customer Information
→ System Checks IMEI/Serial if Required
→ Warranty Start Date Recorded
→ Warranty End Date Computed
→ Warranty Linked to Sale Item
→ Warranty Linked to Customer
→ Warranty Record Created
→ Audit Log Created
```

Rules:

- Warranty must not duplicate for same sale item
- Warranty must link to actual sale
- Warranty must link to product
- Warranty must link to customer
- Warranty must link to IMEI if serialized
- Warranty dates must be computed from sale date
- Warranty auto-registration must fail safely if required data is missing

---

## 6. WARRANTY RECORD MANAGEMENT

Warranty fields:

- warranty_number
- sale_id
- sale_item_id
- customer_id
- product_id
- imei_id nullable
- branch_id
- warranty_start_date
- warranty_end_date
- warranty_status
- coverage_details
- exclusions
- created_by

Warranty statuses:

- Active
- Expired
- Claimed
- Under Review
- Under Repair
- Replaced
- Rejected
- Voided

Features:

- View warranty
- Search warranty
- Update status if authorized
- View warranty timeline
- Print warranty details if needed

---

## 7. WARRANTY LOOKUP

Create warranty lookup tool.

Search by:

- Warranty number
- Receipt number
- Customer name
- Customer mobile number
- IMEI / serial number
- Product name
- Branch

Lookup result must show:

- Warranty status
- Expiration date
- Product details
- Customer details
- Claim history
- Validity status

---

## 8. WARRANTY CLAIM MANAGEMENT

Build warranty claim workflow.

Process flow:

```text
Customer Requests Warranty Claim
→ Staff Searches Warranty Record
→ System Validates Warranty Status
→ Staff Creates Claim
→ Issue Description Recorded
→ Product Condition Recorded
→ Attach Photos / Proof
→ Manager Reviews Claim
→ Approve or Reject Claim
→ If Approved, Repair / Replacement Process Starts
→ Status Updated
→ Customer Notified if Available
→ Audit Log Created
```

Claim fields:

- claim_number
- warranty_id
- customer_id
- branch_id
- claim_date
- issue_description
- product_condition
- claim_status
- reviewed_by nullable
- reviewed_at nullable
- resolution_type nullable
- resolution_notes nullable

Claim statuses:

- Pending
- Under Review
- Approved
- Rejected
- Under Repair
- Ready for Release
- Released
- Replaced
- Cancelled

Rules:

- Claim cannot be created without valid warranty unless authorized
- Expired warranty claim requires special permission
- Rejection requires reason
- Claim status must be logged
- Claim must be audit logged

---

## 9. WARRANTY CLAIM ATTACHMENTS

Support file/photo attachments.

Allowed files:

- JPG
- JPEG
- PNG
- PDF

Attachment fields:

- claim_id
- file_name
- file_path
- file_type
- file_size
- uploaded_by

Rules:

- Validate file type
- Validate file size
- Secure storage
- Authorized preview/download only
- Do not expose warranty files publicly

---

## 10. WARRANTY CLAIM STATUS TIMELINE

Create status timeline.

Track:

- Claim created
- Under review
- Approved/rejected
- Under repair
- Ready for release
- Released
- Replaced
- Cancelled

Timeline fields:

- claim_id
- status
- remarks
- updated_by
- created_at

---

## 11. REPAIR / REPLACEMENT TRACKING FOR WARRANTY

For approved claims, support repair/replacement tracking.

Repair fields:

- claim_id
- repair_details
- technician_name nullable
- repair_start_date nullable
- repair_end_date nullable
- repair_status
- remarks

Replacement fields:

- claim_id
- old_product_id
- old_imei_id nullable
- replacement_product_id nullable
- replacement_imei_id nullable
- replacement_date
- approved_by
- remarks

Rules:

- Replacement must update IMEI/product status properly
- Replacement must be audit logged
- Repair status must be tracked
- Ready for release must be visible in dashboard

---

## 12. WARRANTY EXPIRATION ALERTS

Generate alerts for:

- Warranty near expiration
- Warranty expired
- Pending warranty claim
- Claim overdue
- Repair overdue
- Item ready for release

Alerts must appear in:

- Notification bell
- Warranty dashboard
- Executive dashboard

---

## 13. WARRANTY DASHBOARD

Create warranty dashboard.

Cards:

- Active warranties
- Expired warranties
- Pending claims
- Approved claims
- Under repair
- Ready for release
- Replaced items
- Rejected claims

Charts:

- Warranty claims by status
- Warranty claims per branch
- Warranty claims per product/brand
- Monthly warranty claims

Tables:

- Recent warranty claims
- Expiring warranties
- Ready for release items
- Pending review claims

---

## 14. WARRANTY REPORTS

Create warranty reports.

Reports:

- Active warranty report
- Expired warranty report
- Warranty claims report
- Warranty by branch
- Warranty by product
- Warranty by brand
- Claim resolution report
- Replacement report
- Repair tracking report

Reports must support:

- Date filter
- Branch filter
- Status filter
- Product filter
- Brand filter
- Excel export
- PDF export
- Print view

---

# DATABASE REQUIREMENTS

Create or update migrations:

- customers
- customer_notes
- warranty_rules
- warranties
- warranty_claims
- warranty_claim_attachments
- warranty_claim_status_logs
- warranty_repairs
- warranty_replacements
- customer_notification_preferences

Relationships:

- Customer has many sales
- Customer has many warranties
- Customer has many warranty claims
- Customer has many notes
- Warranty belongs to sale
- Warranty belongs to sale item
- Warranty belongs to customer
- Warranty belongs to product
- Warranty may belong to IMEI
- Warranty belongs to branch
- Warranty claim belongs to warranty
- Warranty claim belongs to customer
- Warranty claim has many attachments
- Warranty claim has many status logs
- Warranty claim may have repair record
- Warranty claim may have replacement record

---

# BACKEND REQUIREMENTS

Create controllers:

- CustomerController
- CustomerProfileController
- CustomerNoteController
- WarrantyRuleController
- WarrantyController
- WarrantyLookupController
- WarrantyClaimController
- WarrantyClaimApprovalController
- WarrantyClaimAttachmentController
- WarrantyRepairController
- WarrantyReplacementController
- WarrantyDashboardController
- WarrantyReportController

Create services:

- CustomerService
- CustomerDuplicateCheckService
- WarrantyRuleService
- WarrantyRegistrationService
- WarrantyLookupService
- WarrantyClaimService
- WarrantyRepairService
- WarrantyReplacementService
- WarrantyAlertService
- WarrantyReportService

Business logic must be inside services.

Controllers must remain clean.

---

# UI/UX REQUIREMENTS

Create responsive screens:

- Customer list
- Customer create/edit form
- Customer profile page
- Customer notes
- Customer purchase history
- Customer warranty history
- Warranty dashboard
- Warranty rule list
- Warranty rule form
- Warranty list
- Warranty details page
- Warranty lookup page
- Warranty claim form
- Warranty claim approval page
- Warranty claim timeline
- Repair tracking page
- Replacement tracking page
- Warranty reports page

UI requirements:

- Clean customer profile layout
- Warranty status badges
- Timeline component for claims
- Responsive tables
- Branch filters
- Search box for IMEI/receipt/customer
- File preview modal
- Mobile-friendly forms
- Clear approval buttons

---

# SECURITY REQUIREMENTS

Implement permissions:

- view_customers
- create_customer
- edit_customer
- deactivate_customer
- view_customer_history
- manage_warranty_rules
- view_warranties
- create_warranty_claim
- approve_warranty_claim
- reject_warranty_claim
- update_warranty_claim_status
- manage_warranty_repair
- manage_warranty_replacement
- view_warranty_reports
- export_warranty_reports

Rules:

- Branch users can only view customer/warranty records connected to allowed branches
- Owner can view all branches
- Warranty rule management is restricted
- Warranty claim approval is restricted
- Expired warranty override is restricted
- File attachments are access-controlled
- Customer information must not be exposed to unauthorized users

---

# AUDIT TRAIL REQUIREMENTS

Log:

- Customer created
- Customer updated
- Customer deactivated
- Customer note added
- Warranty rule created/updated
- Warranty auto-created
- Warranty status changed
- Warranty claim created
- Claim approved/rejected
- Claim status updated
- Repair updated
- Replacement recorded
- Attachment uploaded
- Warranty report exported

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

- New warranty claim
- Warranty claim approved/rejected
- Warranty near expiration
- Warranty expired
- Claim overdue
- Repair overdue
- Item ready for release
- Warranty replacement recorded

---

# VALIDATE SECTION

Validate:

## Customers
- Customer CRUD works
- Duplicate customer detection works
- Customer profile loads correctly
- Purchase history is accurate
- Warranty history is accurate

## Warranty Rules
- Warranty rule creation works
- Product/category/brand rule application works
- Warranty duration computation works

## Warranty Registration
- Warranty auto-creates from POS sale
- Warranty does not duplicate
- Warranty links to customer, sale, product, and IMEI
- Warranty date computation is correct

## Warranty Lookup
- Search by warranty number works
- Search by receipt works
- Search by IMEI works
- Search by customer works

## Claims
- Claim creation works
- Attachment upload works
- Approval/rejection works
- Status timeline works
- Repair tracking works
- Replacement tracking works

## Reports
- Filters work
- Export works
- Print view works

## Security
- Branch restrictions work
- Permissions work
- Attachments are protected
- Unauthorized access is blocked

## UI
- Customer pages are responsive
- Warranty pages are responsive
- Tables do not overflow
- Forms are mobile-friendly

---

# FIX SECTION

If issues are found:

- Fix duplicate customer detection
- Fix warranty rule application
- Fix warranty expiration calculation
- Fix warranty auto-registration
- Fix claim workflow
- Fix attachment security
- Fix repair/replacement tracking
- Fix branch leakage
- Fix permission leaks
- Fix report inaccuracies
- Fix unresponsive warranty pages
- Refactor duplicated warranty logic

Revalidate after fixing.

---

# GATEWAY REVIEW SECTION

Before marking Phase 6 complete, verify:

- global_master.md is followed
- customer module is complete
- customer profile is complete
- warranty rules are database-driven
- warranty auto-registration works
- warranty lookup works
- claim workflow works
- attachment upload is secure
- claim timeline works
- repair/replacement tracking works
- warranty alerts work
- warranty reports work
- branch restrictions are enforced
- audit logs are complete
- no duplicate warranty for same sale item
- no unauthorized customer access
- no unauthorized warranty approval
- no insecure file access
- no broken responsive UI
- no route conflict
- no migration conflict

---

# EXPECTED OUTPUT

At the end of Phase 6, provide:

- Complete customer management module
- Complete customer profile and history
- Complete customer notes
- Complete warranty rule management
- Complete warranty auto-registration
- Complete warranty lookup
- Complete warranty claim workflow
- Complete warranty attachment handling
- Complete claim status timeline
- Complete repair tracking
- Complete replacement tracking
- Complete warranty expiration alerts
- Complete warranty dashboard
- Complete warranty reports
- Updated migrations
- Updated models
- Updated controllers
- Updated services
- Updated routes
- Updated views
- Updated permissions
- Updated audit logs
- Updated notifications
- Responsive customer and warranty UI

PHASE 6 IS NOT COMPLETE UNTIL ALL VALIDATIONS PASS.
````
