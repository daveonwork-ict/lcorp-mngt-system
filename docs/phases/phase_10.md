````markdown
# RC STORE RMS — PHASE 10
# APPROVAL WORKFLOW, AUDIT TRAIL & SECURITY HARDENING
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
DO NOT IGNORE APPROVAL CONTROL.

---

# PHASE OBJECTIVE

Build the complete Approval Workflow, Audit Trail, and Security Hardening Module for RC Store RMS.

This phase must strengthen the system’s control, accountability, approval routing, security monitoring, transaction traceability, and system protection across all existing modules.

This module applies to:

- Inventory adjustments
- Stock transfers
- Expenses
- Fund transfers
- Wallet funding
- Wallet adjustments
- Sales voids
- Sales returns
- Warranty claims
- Purchase requests
- Purchase orders
- Supplier payments
- Office supply issuances

---

# BUILD SECTION

## 1. CENTRALIZED APPROVAL WORKFLOW ENGINE

Create a reusable approval workflow engine.

Approval engine must support:

- Single-level approval
- Multi-level approval
- Branch-based approval
- Role-based approval
- Amount-based approval
- Module-based approval
- Approval escalation preparation
- Returned for correction workflow

Approval statuses:

- Draft
- Pending
- Under Review
- Approved
- Rejected
- Returned for Correction
- Cancelled

---

## 2. APPROVAL REQUEST STRUCTURE

Create centralized approval request records.

Approval request fields:

- approval_number
- module_name
- reference_type
- reference_id
- branch_id
- requested_by
- requested_at
- current_approver_id nullable
- approval_level
- priority
- status
- remarks
- approved_by nullable
- approved_at nullable
- rejected_by nullable
- rejected_at nullable
- rejection_reason nullable
- returned_by nullable
- returned_at nullable
- return_reason nullable

Priority levels:

- Normal
- Important
- Urgent
- Critical

Rules:

- Reference type and reference ID must link to actual transaction
- Approval must respect branch restrictions
- Same requester cannot approve own request unless allowed by configuration
- Rejection requires reason
- Return for correction requires reason
- All approval actions must be audit logged

---

## 3. APPROVAL RULE MANAGEMENT

Create approval rule setup.

Approval rule fields:

- rule_name
- module_name
- transaction_type
- branch_id nullable
- role_id nullable
- minimum_amount nullable
- maximum_amount nullable
- approver_role_id
- approval_level
- requires_owner_approval
- status

Features:

- Create rule
- Edit rule
- Activate/deactivate rule
- Configure approval levels
- Configure approver role
- Configure amount threshold

Rules:

- Rules must be database-driven
- Do not hardcode approvers
- Do not hardcode approval levels
- Approval routing must use configured rules

---

## 4. MULTI-LEVEL APPROVAL SUPPORT

Support multi-level approval.

Example:

```text
Staff Request
→ Branch Manager Review
→ Owner Final Approval
→ Transaction Finalized
```

Rules:

- Each approval level must be logged
- Only authorized approver can approve each level
- Final approval triggers transaction finalization
- Rejection stops workflow
- Return for correction sends back to requester

---

## 5. APPROVAL INBOX

Create approval inbox.

Features:

- View pending approvals
- View approved approvals
- View rejected approvals
- View returned approvals
- Filter by module
- Filter by branch
- Filter by priority
- Filter by date
- View transaction details
- Approve
- Reject
- Return for correction

Approval inbox must show:

- Approval number
- Module
- Requester
- Branch
- Request date
- Priority
- Status
- Current approver

---

## 6. APPROVAL HISTORY TIMELINE

Create approval history timeline.

Track:

- Request created
- Submitted
- Viewed
- Approved
- Rejected
- Returned
- Resubmitted
- Finalized

Timeline fields:

- approval_request_id
- action
- remarks
- performed_by
- performed_at

---

## 7. CENTRALIZED AUDIT TRAIL

Create centralized audit trail module.

Audit fields:

- audit_number
- user_id
- branch_id nullable
- module_name
- action_type
- reference_type nullable
- reference_id nullable
- before_value nullable
- after_value nullable
- ip_address
- user_agent
- device_information nullable
- created_at

Audit features:

- View audit logs
- Filter by user
- Filter by branch
- Filter by module
- Filter by action
- Filter by date range
- Search reference number
- View before/after details
- Export audit logs
- Print audit report

Rules:

- Audit logs must not be editable
- Audit logs must not be deletable by normal users
- Audit access must be restricted
- Audit export must be logged

---

## 8. LOGIN SECURITY MONITORING

Strengthen login monitoring.

Track:

- Successful login
- Failed login
- Logout
- IP address
- User agent
- Device information
- Login timestamp

Features:

- Failed login history
- Suspicious login alert preparation
- Login attempt throttling
- Session regeneration
- Last login update

---

## 9. ACTIVE SESSION MONITORING

Create active session monitoring if supported.

Features:

- View active sessions
- Detect multiple sessions
- Terminate own other sessions
- Admin terminate session if authorized
- Session timeout readiness

Rules:

- Session controls must be permission-protected
- Session termination must be audit logged

---

## 10. FILE ACCESS SECURITY

Secure sensitive files.

Applies to:

- Expense receipts
- Warranty photos
- Supplier proofs
- Announcement attachments
- Chat attachments
- Profile photos
- Report exports

Rules:

- Validate file ownership
- Validate permission before preview/download
- Use secure file serving route where needed
- Log sensitive file download
- Prevent direct unauthorized access

---

## 11. ROUTE SECURITY REVIEW

Review all module routes.

Ensure:

- Auth middleware applied
- Permission middleware applied
- Branch access middleware applied
- Sensitive routes protected
- Export routes protected
- File routes protected
- Approval routes protected

No route should expose restricted data.

---

## 12. VALIDATION HARDENING

Review validation across all modules.

Ensure validation for:

- Required fields
- Numeric fields
- Amounts
- Dates
- Foreign keys
- Status transitions
- Branch ownership
- User permissions
- File uploads
- Duplicate prevention

---

## 13. SECURITY DASHBOARD

Create security dashboard.

Dashboard cards:

- Failed login attempts
- Active users
- Recent audit logs
- Pending approvals
- High-priority approvals
- Suspicious activities
- Recent file downloads
- Recent permission changes

Tables:

- Recent failed logins
- Recent audit activity
- Active sessions
- Recent approval actions
- Security alerts

---

## 14. BACKUP READINESS

Prepare backup monitoring and readiness.

Features:

- Backup log table
- Backup status record
- Manual backup command preparation
- Scheduled backup preparation
- Backup success/failure log
- Restore instruction placeholder

Backup rules:

- Backup access must be restricted
- Backup logs must be audit logged
- Backup files must not be publicly exposed

---

## 15. SECURITY ALERTS

Generate security alerts for:

- Multiple failed login attempts
- Unauthorized access attempt
- Suspicious file access
- Permission changes
- Critical approval request
- Backup failure
- Route access violation

---

# DATABASE REQUIREMENTS

Create or update migrations:

- approval_rules
- approval_requests
- approval_request_logs
- audit_logs
- login_attempt_logs
- user_sessions
- file_access_logs
- backup_logs
- security_alerts

Relationships:

- Approval request belongs to requester/user
- Approval request belongs to branch
- Approval request has many logs
- Approval rule belongs to role/approver role
- Audit log belongs to user
- Audit log may belong to branch
- Login attempt may belong to user
- File access log belongs to user
- Security alert may belong to user

---

# BACKEND REQUIREMENTS

Create controllers:

- ApprovalRuleController
- ApprovalRequestController
- ApprovalInboxController
- ApprovalHistoryController
- AuditTrailController
- LoginSecurityController
- SessionSecurityController
- FileAccessController
- BackupLogController
- SecurityAlertController
- SecurityDashboardController

Create services:

- ApprovalWorkflowService
- ApprovalRoutingService
- ApprovalActionService
- AuditLogService
- SecurityLogService
- LoginSecurityService
- SessionSecurityService
- FileAccessService
- BackupService
- SecurityAlertService

Business logic must be inside services.

Controllers must remain clean.

---

# UI/UX REQUIREMENTS

Create responsive screens:

- Approval rule setup
- Approval inbox
- Approval request details
- Approval history timeline
- Audit trail list
- Audit detail page
- Login activity page
- Active sessions page
- File access logs
- Backup logs
- Security alerts
- Security dashboard

UI requirements:

- Status badges
- Priority badges
- Timeline component
- Branch filters
- Date filters
- Module filters
- Responsive tables
- Clear approve/reject/return buttons
- Confirmation dialogs
- Warning indicators
- Mobile-friendly approval screens

---

# SECURITY REQUIREMENTS

Implement permissions:

- view_approval_inbox
- approve_requests
- reject_requests
- return_requests
- manage_approval_rules
- view_audit_trail
- export_audit_trail
- view_security_dashboard
- view_login_activity
- manage_active_sessions
- view_file_access_logs
- view_backup_logs
- manage_backups
- view_security_alerts

Rules:

- Approval access must follow role and branch restrictions
- Users cannot approve unauthorized modules
- Users cannot approve own request unless configured
- Audit trail access is restricted
- Security dashboard access is restricted
- Backup access is restricted
- File access is restricted
- Direct URL access to protected files is prohibited

---

# AUDIT TRAIL REQUIREMENTS

Log:

- Approval rule created/updated
- Approval request created
- Approval request viewed
- Approval approved
- Approval rejected
- Approval returned
- Approval resubmitted
- Audit report viewed
- Audit report exported
- Login failed
- User session terminated
- File accessed
- Backup generated
- Backup failed
- Security setting changed

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

- New approval request
- Approval approved
- Approval rejected
- Request returned for correction
- High-priority approval
- Suspicious login attempt
- Unauthorized access attempt
- Backup completed
- Backup failed
- Security alert generated

---

# VALIDATE SECTION

Validate:

## Approval Workflow
- Approval rules work
- Approval routing works
- Single-level approval works
- Multi-level approval works
- Reject works
- Return for correction works
- Same-user approval prevention works
- Final approval triggers correct action

## Approval Inbox
- Pending approvals display correctly
- Branch filtering works
- Module filtering works
- Approve/reject actions work
- Timeline records actions

## Audit Trail
- Logs are created
- Filters work
- Before/after values display correctly
- Export works
- Audit logs are protected

## Security
- Login attempts are tracked
- Failed login logs work
- Active session view works if implemented
- Protected files require authorization
- Routes are protected
- Permissions are enforced

## Backup
- Backup logs work
- Backup access is restricted
- Backup actions are logged

## UI
- Approval pages are responsive
- Audit pages are responsive
- Security dashboard is responsive
- Tables do not overflow

---

# FIX SECTION

If issues are found:

- Fix approval routing
- Fix approval status transitions
- Fix unauthorized approval access
- Fix same-user approval prevention
- Fix audit logging gaps
- Fix file access vulnerabilities
- Fix route middleware gaps
- Fix login security tracking
- Fix backup permission issues
- Fix branch leakage
- Fix permission leaks
- Fix unresponsive security pages
- Refactor duplicated security logic

Revalidate after fixing.

---

# GATEWAY REVIEW SECTION

Before marking Phase 10 complete, verify:

- global_master.md is followed
- approval workflow engine is complete
- approval rules are database-driven
- approval inbox works
- multi-level approval works
- approval history works
- audit trail is complete
- login security monitoring works
- file access security works
- route security is enforced
- security dashboard works
- backup readiness exists
- branch restrictions are enforced
- audit logs are complete
- no unauthorized approval access
- no unauthorized audit access
- no protected file exposure
- no route without proper middleware
- no invalid status transitions
- no broken responsive UI
- no route conflict
- no migration conflict

---

# EXPECTED OUTPUT

At the end of Phase 10, provide:

- Complete approval workflow engine
- Complete approval rule setup
- Complete approval request module
- Complete approval inbox
- Complete multi-level approval support
- Complete approval history timeline
- Complete centralized audit trail
- Complete audit filters and export
- Complete login security monitoring
- Complete active session monitoring if applicable
- Complete file access security
- Complete route security review
- Complete validation hardening
- Complete backup readiness
- Complete security dashboard
- Complete security alerts
- Updated migrations
- Updated models
- Updated controllers
- Updated services
- Updated middleware
- Updated routes
- Updated views
- Updated permissions
- Updated audit logs
- Updated notifications
- Responsive approval and security UI

PHASE 10 IS NOT COMPLETE UNTIL ALL VALIDATIONS PASS.
````
