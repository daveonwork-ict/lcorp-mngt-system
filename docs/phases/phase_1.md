# RC STORE RMS — PHASE 1
# USER, ROLES, PERMISSIONS & BRANCH MANAGEMENT
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

Build the complete User, Roles, Permissions, Authentication, and Branch Management foundation for RC Store RMS.

This phase controls:

- User access
- Branch access
- Role-based permission
- Login security
- User accountability
- Branch assignment
- System access control

This phase is required before building inventory, POS, airtime, expenses, reports, and all operational modules.

---

# BUILD SECTION

## 1. USER MANAGEMENT MODULE

Create complete user management.

Features:

- Create user
- Edit user
- View user
- Deactivate user
- Activate user
- Reset password
- Upload profile photo
- Assign role
- Assign branch
- Assign multiple branches if allowed
- Track last login
- Track account status

User fields:

- employee_code
- first_name
- middle_name
- last_name
- suffix
- full_name
- username
- email
- mobile_number
- password
- profile_photo
- primary_branch_id
- status
- last_login_at
- last_login_ip

User statuses:

- Active
- Inactive
- Suspended
- Locked

---

## 2. ROLE MANAGEMENT MODULE

Create role management.

Default roles:

- Owner
- Super Admin
- Branch Manager
- Cashier
- Inventory Staff
- Accounting Staff
- Auditor
- Staff User

Role features:

- Create role
- Edit role
- View role
- Deactivate role
- Assign permissions
- View assigned users

Do not hardcode role logic.

Roles must be database-driven.

---

## 3. PERMISSION MANAGEMENT MODULE

Create permission management.

Permission categories:

### Dashboard
- view_dashboard
- view_executive_dashboard
- view_branch_dashboard

### User Management
- view_users
- create_user
- edit_user
- deactivate_user
- reset_user_password

### Role Management
- view_roles
- create_role
- edit_role
- assign_permissions

### Branch Management
- view_branches
- create_branch
- edit_branch
- deactivate_branch

### Inventory
- view_inventory
- create_product
- edit_product
- stock_in
- stock_adjustment
- transfer_stock

### POS
- view_pos
- create_sale
- void_sale
- refund_sale

### Airtime
- view_airtime
- create_load_transaction
- manage_wallet

### Finance
- view_cash_flow
- create_expense
- approve_expense
- submit_daily_closing

### Warranty
- view_warranty
- create_warranty_claim
- approve_warranty_claim

### Communication
- view_announcements
- create_announcement
- access_chat

### Reports
- view_reports
- export_reports

### Security
- view_audit_logs
- view_security_dashboard

---

## 4. BRANCH MANAGEMENT MODULE

Create complete branch management.

Branch fields:

- branch_code
- branch_name
- address
- contact_number
- email
- manager_id
- opening_time
- closing_time
- operational_status
- status

Branch statuses:

- Active
- Inactive
- Maintenance
- Closed

Features:

- Create branch
- Edit branch
- View branch
- Activate branch
- Deactivate branch
- Assign manager
- Assign users
- View branch users
- View branch operational status

---

## 5. USER-BRANCH ASSIGNMENT

Build user-to-branch assignment.

Requirements:

- User may have one primary branch
- User may have multiple assigned branches if allowed
- Owner can access all branches
- Branch users can only access assigned branches
- Branch restriction must be reusable across modules

Create middleware preparation for:

- branch access validation
- module permission validation
- role validation

---

## 6. AUTHENTICATION SECURITY

Build secure login foundation.

Features:

- Login
- Logout
- Forgot password
- Reset password
- Remember me
- Session regeneration
- Failed login logging
- Login throttling
- Last login tracking

Security requirements:

- Password hashing
- CSRF protection
- Rate limiting
- Session protection
- No plain-text passwords

---

## 7. ACTIVITY LOGGING

Create activity logging for this phase.

Log:

- User login
- User logout
- User created
- User updated
- User deactivated
- Password reset
- Role created
- Role updated
- Permission assigned
- Branch created
- Branch updated
- Branch deactivated
- User branch assignment changed

Activity log fields:

- user_id
- branch_id nullable
- module_name
- action_type
- description
- before_value
- after_value
- ip_address
- user_agent
- created_at

---

## 8. NOTIFICATION FOUNDATION

Create notification foundation.

Features:

- Notification bell
- Unread count
- Read/unread status
- Notification list
- Basic notification model
- User-targeted notification
- Branch-targeted notification

---

## 9. UI SCREENS

Create responsive screens:

### User Screens
- User list
- User create form
- User edit form
- User profile
- User branch assignment

### Role Screens
- Role list
- Role create/edit form
- Permission assignment page

### Branch Screens
- Branch list
- Branch create/edit form
- Branch profile
- Branch user assignment

### Security Screens
- Login history
- Activity logs
- Notification center

---

# DATABASE REQUIREMENTS

Create or update migrations:

- users
- roles
- permissions
- role_permissions
- branches
- user_branches
- activity_logs
- notifications
- password_reset_tokens if needed
- sessions if database sessions are used

Relationships:

- User belongs to primary branch
- User belongs to many branches
- User belongs to role
- Role has many permissions
- Branch has many users
- Activity log belongs to user
- Notification belongs to user nullable
- Notification may belong to branch nullable

---

# BACKEND REQUIREMENTS

Create:

- UserController
- RoleController
- PermissionController
- BranchController
- UserBranchController
- ActivityLogController
- NotificationController
- AuthController if not already available

Create services:

- UserService
- RoleService
- PermissionService
- BranchService
- ActivityLogService
- NotificationService
- BranchAccessService

Business logic must be inside services.

Controllers must remain clean.

---

# UI/UX REQUIREMENTS

All pages must be:

- AdminLTE consistent
- Responsive
- Mobile-friendly
- Touch-friendly
- Clean and professional

Tables must have:

- Search
- Filter
- Pagination
- Status badges
- Action buttons
- Responsive behavior

Forms must have:

- Validation messages
- Required indicators
- Clear labels
- Save/cancel buttons

---

# VALIDATE SECTION

Validate:

## User Management
- User creation works
- User editing works
- User deactivation works
- Password reset works
- Profile upload works
- Branch assignment works

## Role & Permission
- Role creation works
- Permission assignment works
- Permissions are database-driven
- Unauthorized access is blocked

## Branch Management
- Branch creation works
- Branch editing works
- Branch deactivation works
- User assignment works
- Branch filtering works

## Security
- Login works
- Logout works
- Failed login is logged
- Passwords are hashed
- CSRF protection works
- Unauthorized pages are blocked

## Audit
- All critical actions are logged
- Before/after values are stored when applicable
- IP address and user agent are recorded

## Responsiveness
- User pages work on mobile
- Branch pages work on mobile
- Role pages work on mobile
- Tables do not overflow
- Forms are readable

---

# FIX SECTION

If issues are found:

- Fix user validation
- Fix role assignment
- Fix permission middleware
- Fix branch leakage
- Fix login security issues
- Fix broken routes
- Fix unresponsive tables
- Fix broken forms
- Fix missing audit logs
- Fix notification issues
- Refactor duplicated logic

Revalidate after fixing.

---

# GATEWAY REVIEW SECTION

Before marking Phase 1 complete, verify:

- global_master.md is followed
- user management is complete
- role management is complete
- permission management is complete
- branch management is complete
- authentication security is working
- branch restrictions are enforced
- audit logging is working
- notifications foundation exists
- no hardcoded roles
- no hardcoded branch IDs
- no permission leaks
- no branch data leakage
- no broken responsive UI
- no route conflict
- no migration conflict

---

# EXPECTED OUTPUT

At the end of Phase 1, provide:

- Complete user management
- Complete role management
- Complete permission management
- Complete branch management
- Complete user-branch assignment
- Secure authentication foundation
- Activity logging
- Notification foundation
- Middleware preparation
- Responsive UI screens
- Updated migrations
- Updated models
- Updated services
- Updated controllers
- Updated routes
- Updated views
- Updated permissions
- Updated seeders

PHASE 1 IS NOT COMPLETE UNTIL ALL VALIDATIONS PASS.