````markdown
# RC STORE RMS — PHASE 14
# REPAIR / SERVICE MANAGEMENT
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
DO NOT IGNORE CUSTOMER, INVENTORY, WARRANTY, AND POS INTEGRATION.

---

# PHASE OBJECTIVE

Build the complete Repair / Service Management Module for RC Store RMS.

This optional phase must allow RC Store to manage repair and service operations for gadgets, mobile phones, accessories, and related devices.

The module must support:

- Repair ticket creation
- Customer device intake
- Technician assignment
- Diagnosis
- Repair quotation
- Customer approval
- Parts usage
- Service charges
- Repair payments
- Repair status tracking
- Device release
- Service warranty
- Repair reports

This phase depends on:

- Phase 1 — User, Roles, Permissions & Branch Management
- Phase 2 — Product & Inventory Management
- Phase 3 — POS & Sales Management
- Phase 5 — Cash Flow, Expenses & Daily Closing
- Phase 6 — Warranty & Customer Management

---

# BUILD SECTION

## 1. REPAIR TICKET MANAGEMENT

Create repair ticket workflow.

Process flow:

```text
Customer Requests Repair
→ Staff Searches or Creates Customer
→ Staff Records Device Details
→ Staff Records Issue Description
→ Device Condition Recorded
→ Repair Ticket Created
→ Technician Assigned
→ Diagnosis Performed
→ Quotation Prepared
→ Customer Approves or Declines
→ Repair Proceeds if Approved
→ Parts and Service Charges Recorded
→ Payment Recorded
→ Device Released
→ Service Warranty Created if Applicable
```

Repair ticket fields:

- repair_ticket_number
- branch_id
- customer_id
- sale_id nullable
- warranty_id nullable
- product_id nullable
- imei_id nullable
- device_brand
- device_model
- serial_number nullable
- issue_description
- device_condition
- accessories_received
- estimated_cost nullable
- final_cost nullable
- assigned_technician_id nullable
- repair_status
- received_by
- released_by nullable
- received_at
- released_at nullable
- remarks

---

## 2. REPAIR STATUS WORKFLOW

Support repair statuses:

- Received
- Under Diagnosis
- Awaiting Customer Approval
- Approved for Repair
- Declined by Customer
- Under Repair
- Waiting for Parts
- Repaired
- Not Repairable
- Ready for Release
- Released
- Cancelled

Rules:

- Status changes must be logged
- Invalid status transitions must be blocked
- Released status requires payment validation if charges exist
- Released status requires authorized user
- Cancelled repair must require reason

---

## 3. CUSTOMER AND DEVICE INTAKE

Create repair intake screen.

Required intake details:

- Customer information
- Device brand
- Device model
- IMEI/serial number if available
- Accessories received
- Physical condition
- Issue description
- Photos optional
- Initial remarks
- Received by
- Branch

Rules:

- Existing customer should be searchable
- New customer can be created if authorized
- If device was purchased from RC Store, link sale/warranty if found
- If device is under warranty, allow warranty-related repair tagging

---

## 4. TECHNICIAN MANAGEMENT / ASSIGNMENT

Allow technician assignment.

Features:

- Assign technician
- Reassign technician
- Track technician workload
- Track technician repair status
- Add technician notes

Rules:

- Technician must be an authorized user or configured staff record
- Reassignment must be logged
- Technician can only view assigned repair tickets unless authorized

---

## 5. DIAGNOSIS MANAGEMENT

Create diagnosis workflow.

Diagnosis fields:

- repair_ticket_id
- technician_id
- diagnosis_findings
- recommended_action
- estimated_cost
- estimated_completion_date
- customer_approval_required
- technician_notes
- diagnosed_at

Rules:

- Diagnosis must be logged
- Diagnosis may trigger customer approval requirement
- Estimated cost must be numeric
- Diagnosis update must be permission-controlled

---

## 6. CUSTOMER APPROVAL FOR REPAIR

Create customer approval process.

Process flow:

```text
Technician Adds Diagnosis and Estimated Cost
→ Staff Presents Estimate to Customer
→ Customer Approves or Declines
→ Approval Status Recorded
→ Repair Continues or Device Returned
```

Approval fields:

- repair_ticket_id
- approval_status
- approved_by_customer_name
- approval_date
- approval_notes
- recorded_by

Statuses:

- Pending
- Approved
- Declined

Rules:

- Repair with estimated cost must require customer approval if configured
- Declined repair should move ticket to Declined by Customer
- Customer approval must be logged

---

## 7. REPAIR PARTS USED

Create parts usage tracking.

Features:

- Select inventory item/part
- Deduct used quantity
- Record cost
- Link part to repair ticket
- Track parts used by technician

Repair part fields:

- repair_ticket_id
- product_id
- imei_id nullable
- quantity_used
- unit_cost
- total_cost
- used_by
- used_at
- remarks

Rules:

- Parts must come from available inventory
- Quantity cannot exceed available stock
- Serialized parts must require IMEI/serial
- Parts usage must create inventory movement
- Parts deduction must be audit logged

---

## 8. SERVICE CHARGES

Create service charge management.

Charge types:

- Diagnosis fee
- Labor fee
- Parts fee
- Other service charge
- Discount if authorized

Repair charge fields:

- repair_ticket_id
- charge_type
- description
- amount
- created_by
- created_at

Rules:

- Charges must compute final cost
- Discount requires permission
- Charges must appear in repair billing

---

## 9. REPAIR PAYMENT MANAGEMENT

Support repair payment recording.

Payment fields:

- repair_ticket_id
- payment_method_id
- amount_paid
- reference_number nullable
- received_by
- received_at
- remarks

Rules:

- Payment must update repair balance
- Payment must integrate with cash-in/financial ledger
- Payment cannot exceed balance unless authorized
- Payment must be audit logged

---

## 10. DEVICE RELEASE

Create device release workflow.

Process flow:

```text
Repair Completed / Not Repairable / Declined
→ Staff Validates Status
→ Staff Validates Payment if Required
→ Release Details Recorded
→ Customer Acknowledgment Recorded
→ Device Released
→ Service Warranty Created if Applicable
```

Release fields:

- repair_ticket_id
- released_by
- released_to
- released_at
- release_remarks
- customer_acknowledgment

Rules:

- Device cannot be released if payment is required but unpaid
- Device release requires permission
- Released repair cannot be edited except by authorized correction
- Release must be audit logged

---

## 11. SERVICE WARRANTY

Create service warranty for repaired items if applicable.

Service warranty fields:

- repair_ticket_id
- customer_id
- warranty_start_date
- warranty_end_date
- warranty_terms
- warranty_status

Statuses:

- Active
- Expired
- Voided

Rules:

- Service warranty duration must be configurable
- Warranty starts on release date
- Service warranty must be linked to repair ticket

---

## 12. REPAIR ATTACHMENTS

Support repair-related attachments.

Allowed files:

- JPG
- JPEG
- PNG
- PDF

Attachment use cases:

- Device intake photos
- Damage photos
- Diagnosis proof
- Customer approval proof
- Release proof

Rules:

- Validate file type
- Validate file size
- Store securely
- Authorized preview/download only

---

## 13. REPAIR DASHBOARD

Create repair dashboard.

Cards:

- Total repair tickets
- Pending diagnosis
- Awaiting customer approval
- Under repair
- Waiting for parts
- Ready for release
- Released repairs
- Repair revenue

Charts:

- Repair tickets by status
- Repair revenue trend
- Technician workload
- Repairs per branch
- Common repair issues

Tables:

- Recent repair tickets
- Pending diagnosis
- Ready for release
- Overdue repairs
- Technician assignments

---

## 14. REPAIR REPORTS

Create repair reports.

Reports:

- Repair ticket report
- Technician workload report
- Repair revenue report
- Parts used report
- Pending repair report
- Released repair report
- Service warranty report
- Common issue report

Reports must support:

- Date filter
- Branch filter
- Technician filter
- Status filter
- Customer filter
- Export to Excel
- Export to PDF
- Print view

---

# DATABASE REQUIREMENTS

Create or update migrations:

- repair_tickets
- repair_diagnoses
- repair_customer_approvals
- repair_status_logs
- repair_parts
- repair_charges
- repair_payments
- repair_releases
- service_warranties
- repair_attachments

Relationships:

- Repair ticket belongs to branch
- Repair ticket belongs to customer
- Repair ticket may belong to sale
- Repair ticket may belong to warranty
- Repair ticket may belong to product
- Repair ticket may belong to IMEI
- Repair ticket has many diagnoses
- Repair ticket has many status logs
- Repair ticket has many parts
- Repair ticket has many charges
- Repair ticket has many payments
- Repair ticket has one release
- Repair ticket may have service warranty
- Repair ticket has many attachments

---

# BACKEND REQUIREMENTS

Create controllers:

- RepairTicketController
- RepairDiagnosisController
- RepairCustomerApprovalController
- RepairTechnicianController
- RepairPartsController
- RepairChargeController
- RepairPaymentController
- RepairReleaseController
- ServiceWarrantyController
- RepairAttachmentController
- RepairDashboardController
- RepairReportController

Create services:

- RepairTicketService
- RepairStatusService
- RepairDiagnosisService
- RepairApprovalService
- RepairPartsService
- RepairChargeService
- RepairPaymentService
- RepairReleaseService
- ServiceWarrantyService
- RepairReportService
- RepairInventoryIntegrationService
- RepairFinanceIntegrationService

Business logic must be inside services.

Controllers must remain clean.

---

# UI/UX REQUIREMENTS

Create responsive screens:

- Repair dashboard
- Repair ticket list
- Repair ticket create form
- Repair ticket detail page
- Device intake form
- Diagnosis form
- Customer approval page
- Technician assignment page
- Parts used page
- Service charges page
- Repair payment page
- Device release page
- Service warranty page
- Repair reports page

UI requirements:

- Repair status badges
- Timeline layout
- Customer/device summary card
- Responsive tables
- Mobile-friendly forms
- Attachment preview
- Clear status action buttons
- Technician workload panel
- Payment summary panel

---

# SECURITY REQUIREMENTS

Implement permissions:

- view_repairs
- create_repair_ticket
- edit_repair_ticket
- assign_repair_technician
- update_repair_diagnosis
- record_customer_repair_approval
- record_repair_parts
- record_repair_charges
- record_repair_payment
- release_device
- manage_service_warranty
- view_repair_reports
- export_repair_reports

Rules:

- Branch users can only access assigned branch repairs
- Technicians can only access assigned repairs unless authorized
- Device release is restricted
- Repair payments are restricted
- Parts usage is restricted
- Attachment access is protected
- Customer data must be protected

---

# AUDIT TRAIL REQUIREMENTS

Log:

- Repair ticket created
- Repair ticket updated
- Technician assigned
- Diagnosis recorded
- Customer approval recorded
- Repair status changed
- Part used
- Charge added
- Payment recorded
- Device released
- Service warranty created
- Attachment uploaded
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

- New repair ticket
- Technician assigned
- Diagnosis completed
- Awaiting customer approval
- Repair ready for release
- Repair overdue
- Payment pending
- Device released

---

# VALIDATE SECTION

Validate:

## Repair Ticket
- Repair ticket creation works
- Customer linking works
- Device details save correctly
- Status workflow works

## Technician
- Assignment works
- Technician restrictions work
- Workload display works

## Diagnosis
- Diagnosis recording works
- Customer approval works
- Declined repair workflow works

## Parts
- Parts usage works
- Inventory deduction works
- Serialized parts validation works
- Parts ledger works

## Billing
- Charges compute correctly
- Payment works
- Balance updates correctly
- Cash flow integration works

## Release
- Device release works
- Payment validation works
- Service warranty generation works

## Reports
- Filters work
- Export works
- Print view works

## Security
- Branch restrictions work
- Permissions work
- Attachments protected
- Unauthorized access blocked

## UI
- Repair pages are responsive
- Forms are mobile-friendly
- Tables do not overflow

---

# FIX SECTION

If issues are found:

- Fix repair status workflow
- Fix customer linking
- Fix technician assignment
- Fix diagnosis flow
- Fix customer approval status
- Fix parts deduction
- Fix billing computation
- Fix payment validation
- Fix device release logic
- Fix service warranty date
- Fix branch leakage
- Fix permission leaks
- Fix attachment security
- Fix report inaccuracies
- Fix responsive issues
- Refactor duplicated repair logic

Revalidate after fixing.

---

# GATEWAY REVIEW SECTION

Before marking Phase 14 complete, verify:

- global_master.md is followed
- repair ticket module is complete
- device intake works
- technician assignment works
- diagnosis works
- customer approval works
- parts usage works
- inventory deduction works
- service charges work
- repair payment works
- device release works
- service warranty works
- reports work
- branch restrictions are enforced
- audit logs are complete
- no unauthorized repair access
- no unauthorized device release
- no incorrect parts deduction
- no incorrect repair billing
- no insecure attachment access
- no broken responsive UI
- no route conflict
- no migration conflict

---

# EXPECTED OUTPUT

At the end of Phase 14, provide:

- Complete repair ticket module
- Complete customer/device intake
- Complete technician assignment
- Complete diagnosis workflow
- Complete customer approval workflow
- Complete parts used tracking
- Complete inventory integration
- Complete service charge management
- Complete repair payment management
- Complete device release workflow
- Complete service warranty management
- Complete repair attachments
- Complete repair dashboard
- Complete repair reports
- Updated migrations
- Updated models
- Updated controllers
- Updated services
- Updated routes
- Updated views
- Updated permissions
- Updated audit logs
- Updated notifications
- Responsive repair/service UI

PHASE 14 IS NOT COMPLETE UNTIL ALL VALIDATIONS PASS.
````
