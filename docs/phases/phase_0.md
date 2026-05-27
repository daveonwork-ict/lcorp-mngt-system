# RC STORE RMS — PHASE 0
# UI/UX PROTOTYPE & SYSTEM FOUNDATION
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
DO NOT IGNORE PWA READINESS.

---

# PHASE OBJECTIVE

Build the complete UI/UX prototype and system foundation for the RC Store RMS.

This phase is focused on creating the visual, structural, and technical foundation of the system before building the business modules.

The system must be prepared as a:

- Cross-platform web-based system
- PWA-ready application
- Multi-branch retail management platform
- Enterprise-grade AdminLTE-based dashboard
- Mobile-responsive and touchscreen-friendly application

---

# SYSTEM CONTEXT

RC Store is a multi-branch retail business engaged in:

- Mobile phone sales
- Gadget sales
- Accessories sales
- Beauty products
- Digital load / airtime
- Office supplies
- Warranty handling
- Expenses
- Cash monitoring
- Sales monitoring
- Branch monitoring

The system must replace manual and Excel-based monitoring with centralized digital workflows.

---

# BUILD SECTION

## 1. PROJECT STRUCTURE FOUNDATION

Create or prepare the Laravel project structure following the standards in `global_master.md`.

Required structure:

```text
app/
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│   └── Requests/
├── Models/
├── Services/
├── Repositories/
├── Policies/
├── Notifications/
└── Traits/

resources/
├── views/
├── css/
└── js/

routes/
├── web.php
├── auth.php
└── module route files if needed

database/
├── migrations/
└── seeders/

public/
├── manifest.json
└── sw.js
```

---

## 2. ADMINLTE LAYOUT FOUNDATION

Integrate AdminLTE as the main backend interface.

Create the following layout files:

```text
resources/views/layouts/app.blade.php
resources/views/layouts/auth.blade.php
resources/views/layouts/partials/sidebar.blade.php
resources/views/layouts/partials/topbar.blade.php
resources/views/layouts/partials/footer.blade.php
resources/views/layouts/partials/notification-bell.blade.php
resources/views/layouts/partials/branch-switcher.blade.php
```

The layout must include:

- Fixed or collapsible sidebar
- Top navigation bar
- Notification bell
- User profile dropdown
- Branch selector/switcher
- Breadcrumb area
- Content wrapper
- Footer
- Responsive mobile menu

---

## 3. GLOBAL NAVIGATION MENU

Create a sidebar navigation prototype with these modules:

- Dashboard
- POS
- Inventory
- Airtime / Digital Load
- Cash Flow
- Expenses
- Warranty
- Customers
- Suppliers
- Purchasing
- Office Supplies
- Announcements
- Chat
- Reports
- Approvals
- Audit Trail
- Users & Roles
- Branches
- Settings

All menu items must be prepared for permission-based visibility.

Do not hardcode visibility logic. Prepare it for RBAC.

---

## 4. LOGIN PAGE UI

Create a professional login page.

Login page must include:

- RC Store RMS branding
- Username/email field
- Password field
- Remember me
- Forgot password link
- Login button
- Responsive design
- Clean retail-management style

Security preparation:

- CSRF protection
- Error message area
- Rate-limit readiness
- Session security readiness

---

## 5. OWNER EXECUTIVE DASHBOARD PROTOTYPE

Create an executive dashboard prototype.

Dashboard cards:

- Today’s Sales
- Monthly Sales
- Total Expenses
- Net Income
- Cash Position
- Inventory Value
- Low Stock Items
- Airtime Wallet Balance
- Pending Approvals
- Warranty Claims
- Unread Announcements
- Active Branches

Dashboard charts:

- Sales per Branch
- Sales Trend
- Expense Trend
- Inventory Value per Branch
- Airtime Sales by Provider
- Branch Performance Ranking

Dashboard tables:

- Recent Sales
- Low Stock Items
- Pending Approvals
- Recent Expenses
- Recent Announcements

All data may use safe demo placeholders for UI prototype only, but structure must be ready for real dynamic data.

---

## 6. BRANCH DASHBOARD PROTOTYPE

Create branch-level dashboard prototype.

Must show:

- Branch today sales
- Branch expenses
- Branch cash status
- Branch inventory alerts
- Branch airtime wallet status
- Branch pending tasks
- Branch daily closing status

---

## 7. POS UI PROTOTYPE

Create touchscreen-friendly POS screen.

POS UI must include:

- Product search bar
- Barcode input area
- Product grid
- Cart panel
- Quantity controls
- Discount area
- Payment method section
- Checkout button
- Hold transaction button
- Clear cart button
- Receipt preview area

Design must be:

- Touch-friendly
- Large buttons
- Minimal clicks
- Responsive
- POS touchscreen-ready

---

## 8. INVENTORY UI PROTOTYPE

Create inventory prototype screens:

- Product masterlist
- Product create/edit form
- Category list
- Brand list
- Stock-in page
- Stock transfer page
- Stock adjustment page
- Inventory movement ledger
- Low stock page

Inventory UI must include:

- Branch filter
- Category filter
- Search
- Status badges
- Responsive table
- Action buttons

---

## 9. AIRTIME UI PROTOTYPE

Create airtime module prototype screens:

- Airtime dashboard
- Provider list
- Wallet balance page
- Load transaction page
- Wallet funding page
- Airtime report page

Must show:

- Wallet balance per provider
- Load sales today
- Commission
- Low wallet alert
- Recent load transactions

---

## 10. CASH FLOW & EXPENSE UI PROTOTYPE

Create prototype screens:

- Cash flow dashboard
- Opening cash form
- Cash-in list
- Cash-out list
- Expense category list
- Expense encoding form
- Expense approval page
- Daily closing page
- Cash variance page

---

## 11. WARRANTY & CUSTOMER UI PROTOTYPE

Create prototype screens:

- Customer list
- Customer profile
- Customer purchase history
- Warranty list
- Warranty lookup
- Warranty claim form
- Warranty claim status timeline

---

## 12. ANNOUNCEMENT & CHAT UI PROTOTYPE

Create communication prototype screens:

- Announcement list
- Announcement create form
- Announcement detail page
- Announcement read tracking
- Chat room list
- Chat conversation screen
- Notification center

Chat UI must be:

- Mobile-friendly
- Branch-aware
- Group-chat ready
- Private-message ready

---

## 13. REPORTS UI PROTOTYPE

Create reports dashboard prototype.

Report categories:

- Sales Reports
- Inventory Reports
- Airtime Reports
- Expense Reports
- Cash Flow Reports
- Warranty Reports
- Branch Reports
- Audit Reports

Each report screen must prepare:

- Date filter
- Branch filter
- Status filter
- Export buttons
- Print button

---

## 14. PWA FOUNDATION

Create PWA readiness files:

```text
public/manifest.json
public/sw.js
```

PWA must prepare:

- App name
- Short name
- Icons
- Theme color
- Start URL
- Display mode
- Offline fallback readiness

Do not cache sensitive transaction data.

---

## 15. BASIC DATABASE FOUNDATION

Create initial migrations for:

- branches
- roles
- permissions
- users
- role_permissions
- user_branches
- activity_logs
- notifications
- system_settings

Do not overbuild business modules yet.

---

## 16. BASIC SEEDERS

Create seeders for:

- Default roles
- Default permissions
- Default owner account
- Default branch placeholder
- Default system settings

Seeders must be safe and should not overwrite production data.

---

## 17. ROUTE FOUNDATION

Prepare route structure:

```text
routes/web.php
routes/auth.php
routes/dashboard.php
routes/inventory.php
routes/pos.php
routes/airtime.php
routes/finance.php
routes/warranty.php
routes/communication.php
routes/reports.php
routes/admin.php
```

Routes must be grouped by:

- auth middleware
- verified middleware if applicable
- permission middleware preparation
- branch access middleware preparation

---

## 18. RESPONSIVE DESIGN FOUNDATION

Ensure all UI prototypes are responsive for:

- Desktop
- Laptop
- Tablet
- Android phone
- iPhone
- POS touchscreen

Strictly avoid:

- Horizontal overflow
- Broken tables
- Small buttons
- Unreadable text
- Broken charts
- Broken sidebar behavior

---

# VALIDATE SECTION

Validate the following:

## UI Validation

- Login page loads correctly
- Sidebar works
- Topbar works
- Dashboard cards display correctly
- Charts resize properly
- Tables are responsive
- Mobile view is usable
- POS layout is touchscreen-friendly
- Chat layout is mobile-friendly

## Architecture Validation

- Laravel structure is clean
- AdminLTE integration is stable
- Route files are organized
- Views are modular
- Partials are reusable
- Services folder exists
- Repositories folder exists if used
- Middleware structure is ready

## PWA Validation

- manifest.json loads
- sw.js does not break the app
- app is installable where supported
- no sensitive pages are cached
- mobile meta tags are correct

## Database Validation

- migrations run successfully
- seeders run safely
- foreign keys are valid
- tables use timestamps
- relationships are prepared

## Security Validation

- CSRF enabled
- login page protected
- routes prepared for middleware
- no public access to protected pages
- no hardcoded credentials
- no hardcoded branch IDs

---

# FIX SECTION

If issues are found:

- Fix broken layout
- Fix sidebar responsiveness
- Fix route errors
- Fix migration errors
- Fix seeder errors
- Fix PWA manifest errors
- Fix unsafe service worker caching
- Fix UI overflow
- Fix mobile usability issues
- Fix inconsistent UI components
- Fix missing permissions preparation
- Fix missing branch preparation

After fixing, revalidate all affected areas.

---

# GATEWAY REVIEW SECTION

Before marking Phase 0 complete, verify:

- global_master.md is followed
- AdminLTE layout is working
- UI prototype screens are complete
- Sidebar navigation is complete
- Dashboard prototype is complete
- POS prototype is complete
- Inventory prototype is complete
- Airtime prototype is complete
- Finance prototype is complete
- Warranty prototype is complete
- Communication prototype is complete
- Reports prototype is complete
- PWA foundation exists
- Database foundation exists
- Route foundation exists
- No hardcoded business logic
- No broken responsive layout
- No security regression
- No route conflict
- No migration conflict

---

# EXPECTED OUTPUT

At the end of Phase 0, provide:

- Complete AdminLTE layout
- Complete responsive application shell
- Complete login UI
- Complete dashboard prototype
- Complete POS UI prototype
- Complete inventory UI prototype
- Complete airtime UI prototype
- Complete cash/expense UI prototype
- Complete warranty/customer UI prototype
- Complete announcement/chat UI prototype
- Complete reports UI prototype
- PWA foundation
- Initial migrations
- Initial seeders
- Organized routes
- Reusable Blade partials
- Responsive UI foundation
- Security-ready structure

PHASE 0 IS NOT COMPLETE UNTIL ALL VALIDATIONS PASS.