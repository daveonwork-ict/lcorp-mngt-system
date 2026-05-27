# global_master.md
# RC STORE RMS — GLOBAL MASTER DEVELOPMENT GUIDE
# STRICT ENTERPRISE DEVELOPMENT STANDARD

---

# SYSTEM NAME

RC Store RMS  
(Retail Management System)

---

# SYSTEM OVERVIEW

Build a complete:

- Cross-Platform
- Multi-Branch
- Enterprise Retail Management System

for RC Store.

The system supports:

- Gadget sales
- Mobile phones
- Digital load / airtime
- Beauty products
- Accessories
- Office supplies
- Warranty management
- Inventory monitoring
- Expense monitoring
- Cash flow
- Executive analytics
- Communication
- Reports
- PWA support

---

# CORE OBJECTIVE

The primary objective of this system is to:

- Streamline daily store operations
- Centralize multi-branch monitoring
- Replace manual Excel monitoring
- Reduce operational errors
- Improve accountability
- Improve reporting
- Improve inventory accuracy
- Improve sales monitoring
- Improve cash flow visibility
- Provide real-time executive monitoring
- Prepare scalable business operations

---

# DEVELOPMENT RULES

STRICTLY FOLLOW ALL RULES.

DO NOT IGNORE.

---

# NO HALLUCINATION RULE

DO NOT:

- Invent features not requested
- Invent fake APIs
- Invent unsupported workflows
- Invent non-existing database fields
- Invent fake packages
- Invent unsupported integrations
- Assume unsupported business logic

IF SOMETHING IS UNCLEAR:
- create configurable architecture
- create scalable structure
- create future-ready implementation

DO NOT fabricate logic.

---

# NO HARD CODING RULE

STRICTLY PROHIBITED:

- hardcoded branch IDs
- hardcoded roles
- hardcoded permissions
- hardcoded API keys
- hardcoded business rules
- hardcoded statuses
- hardcoded computations
- hardcoded URLs

Everything MUST be configurable.

---

# ENTERPRISE ARCHITECTURE RULE

The system MUST follow enterprise-grade architecture.

STRICTLY IMPLEMENT:

- Service Layer
- Repository Pattern where applicable
- Request Validation
- Middleware Protection
- Role-Based Access Control (RBAC)
- Audit Logging
- Notification System
- Modular Structure
- Responsive Design
- Secure Upload Handling

---

# REQUIRED TECH STACK

STRICTLY USE:

## Backend
- Laravel

## Database
- MySQL / MariaDB

## Frontend
- AdminLTE
- Bootstrap
- Tailwind enhancements where needed
- Alpine.js if needed

## Charts
- Chart.js

## Build Tool
- Vite

## Cross Platform
- PWA-ready architecture

---

# UI/UX STANDARDS

ALL UI MUST BE:

- Modern
- Clean
- Enterprise-grade
- Responsive
- Mobile-friendly
- Touchscreen-friendly
- PWA-ready

---

# RESPONSIVENESS RULE

STRICTLY SUPPORT:

- Desktop
- Laptop
- Android
- iPhone
- Tablet
- POS Touchscreen

NO BROKEN UI.

NO HORIZONTAL OVERFLOW.

NO UNRESPONSIVE TABLES.

---

# MULTI-BRANCH RULE

The entire system MUST support:

- Multiple branches
- Branch restrictions
- Branch filtering
- Branch-specific dashboards
- Branch-specific permissions
- Branch-specific reports

Branch isolation MUST be enforced.

---

# ROLE-BASED ACCESS CONTROL RULE

STRICTLY IMPLEMENT:

- Roles
- Permissions
- Middleware protection
- Branch restrictions

NO unauthorized access.

---

# SECURITY RULES

STRICTLY IMPLEMENT:

- CSRF protection
- XSS prevention
- SQL injection prevention
- File upload validation
- Session protection
- Secure authentication
- Password hashing
- Permission middleware
- Secure routes
- Audit trails

---

# AUDIT TRAIL RULE

ALL critical actions MUST be logged.

INCLUDING:

- Login
- Logout
- Create
- Update
- Delete
- Approvals
- Exports
- Uploads
- Transactions
- Adjustments
- Transfers

Audit logs MUST include:

- user
- branch
- module
- action
- before value
- after value
- IP address
- timestamp

---

# NOTIFICATION RULE

The system MUST support notifications.

INCLUDING:

- Approvals
- Low stock
- Wallet alerts
- Expense alerts
- Cash variance
- Warranty alerts
- Announcements
- Chat messages

---

# FILE UPLOAD RULE

STRICTLY VALIDATE:

- file type
- file size
- file ownership
- secure storage

DO NOT expose sensitive uploads publicly.

---

# DATABASE RULES

STRICTLY IMPLEMENT:

- Foreign keys
- Indexes
- Proper relationships
- Soft deletes where applicable
- Migration consistency
- Seeder consistency

DO NOT duplicate unnecessary data.

---

# SERVICE LAYER RULE

BUSINESS LOGIC MUST BE INSIDE SERVICES.

Controllers MUST remain clean.

DO NOT overload controllers.

---

# VALIDATION RULE

STRICTLY VALIDATE:

- required fields
- numeric values
- statuses
- ownership
- permissions
- branch access
- uploads
- computations
- date ranges

---

# REPORT RULE

ALL REPORTS MUST SUPPORT:

- Excel Export
- PDF Export
- Print View
- Date Filtering
- Branch Filtering
- Status Filtering

---

# PWA RULE

The system MUST support:

- Installable application
- Responsive layout
- Offline fallback page
- Push notification readiness
- Touchscreen optimization

DO NOT cache sensitive transaction data insecurely.

---

# PERFORMANCE RULE

STRICTLY IMPLEMENT:

- Optimized queries
- Pagination
- Lazy loading
- Query optimization
- Caching where safe
- No N+1 queries

---

# DEVELOPMENT PROCESS RULE

EVERY PHASE MUST FOLLOW:

1. BUILD
2. VALIDATE
3. FIX
4. GATEWAY REVIEW

NO phase is complete without validation.

---

# BUILD RULE

During BUILD:

- create complete implementation
- create responsive UI
- create migrations
- create models
- create controllers
- create services
- create routes
- create permissions
- create validations
- create audit logging
- create notifications

---

# VALIDATE RULE

During VALIDATION:

STRICTLY TEST:

- CRUD operations
- permissions
- branch restrictions
- responsiveness
- reports
- exports
- uploads
- workflows
- calculations
- notifications
- audit logs

---

# FIX RULE

During FIX:

- fix all detected issues
- refactor duplicated logic
- optimize queries
- improve responsiveness
- improve validations
- improve security

REVALIDATE after every fix.

---

# GATEWAY REVIEW RULE

Before phase completion:

VERIFY:

- no broken routes
- no broken UI
- no unauthorized access
- no incorrect calculations
- no branch leakage
- no missing validations
- no missing permissions
- no missing audit logs
- no insecure uploads
- no migration conflicts
- no responsiveness issues

---

# CODE QUALITY RULE

STRICTLY FOLLOW:

- clean code
- reusable components
- modular architecture
- proper naming conventions
- readable code
- scalable structure

---

# NAMING CONVENTION RULE

STRICTLY USE:

## Tables
snake_case plural

Example:
- products
- inventory_movements

## Models
PascalCase singular

Example:
- Product
- InventoryMovement

## Controllers
PascalCase + Controller

Example:
- ProductController

## Services
PascalCase + Service

Example:
- ProductService

---

# FINAL OBJECTIVE

The final system MUST become:

A scalable, enterprise-grade, cross-platform, multi-branch Retail Management System capable of supporting RC Store’s long-term operations, centralized monitoring, reporting, communication, operational security, and future business expansion.

---

# STRICT FINAL RULE

EVERY PHASE PROMPT MUST INCLUDE:

"STRICTLY FOLLOW global_master.md"

NO EXCEPTIONS.