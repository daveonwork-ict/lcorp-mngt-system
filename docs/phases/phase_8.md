```markdown
# RC STORE RMS — PHASE 8
# REPORTS, ANALYTICS & EXECUTIVE DASHBOARD
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
DO NOT IGNORE DATA ACCURACY.

---

# PHASE OBJECTIVE

Build the complete Reports, Analytics, and Executive Dashboard Module for RC Store RMS.

This phase must centralize operational visibility for management and provide accurate reporting across all branches.

The module must provide:

- Executive dashboards
- Branch dashboards
- Sales analytics
- Inventory analytics
- Airtime analytics
- Financial analytics
- Warranty analytics
- User activity analytics
- Exportable reports
- Printable reports
- Branch performance monitoring

This module depends on:

- Phase 2 — Inventory
- Phase 3 — POS
- Phase 4 — Airtime
- Phase 5 — Finance
- Phase 6 — Warranty
- Phase 7 — Communication

---

# BUILD SECTION

## 1. EXECUTIVE DASHBOARD

Create enterprise executive dashboard.

Dashboard visibility:

- Owner
- Super Admin
- Authorized Executives

Dashboard cards:

- Today’s total sales
- Monthly total sales
- Total expenses
- Net income estimate
- Branch cash position
- Inventory value
- Low stock items
- Total airtime sales
- Total airtime commissions
- Pending approvals
- Pending warranty claims
- Active announcements
- Unresolved cash variances

---

## 2. EXECUTIVE DASHBOARD CHARTS

Create analytics charts.

Charts:

### Sales Analytics
- Sales trend
- Sales per branch
- Sales per category
- Sales per brand
- Top selling products
- Sales by payment method
- Sales by cashier

### Inventory Analytics
- Inventory value per branch
- Inventory movement trend
- Low stock trend
- Dead stock analysis
- Fast-moving products
- Slow-moving products

### Airtime Analytics
- Airtime sales per provider
- Wallet balance per branch
- Commission trend
- Failed/reversed transactions

### Financial Analytics
- Expense trend
- Expense by category
- Cash variance trend
- Branch cash comparison

### Warranty Analytics
- Warranty claims trend
- Claims by branch
- Claims by product
- Replacement analysis

---

## 3. EXECUTIVE DASHBOARD TABLES

Create executive summary tables.

Tables:

- Recent sales
- Recent expenses
- Pending approvals
- Low stock products
- Pending transfers
- Pending warranty claims
- Pending daily closings
- Suspicious airtime transactions
- Top performing branches
- Underperforming branches

---

## 4. BRANCH DASHBOARD

Create branch-level dashboard.

Cards:

- Branch today sales
- Branch monthly sales
- Branch expenses
- Branch cash position
- Branch low stock items
- Branch airtime sales
- Branch pending approvals
- Branch unresolved variance

Charts:

- Branch sales trend
- Branch expense trend
- Branch airtime trend
- Branch inventory movement

Tables:

- Recent branch sales
- Recent branch expenses
- Pending branch approvals
- Low stock items

Rules:

- Branch users can only view assigned branch dashboard
- Owner can switch between branches
- Dashboard filters must respect branch permissions

---

## 5. REPORT CENTER

Create centralized report center.

Report groups:

### Sales Reports
- Daily sales
- Monthly sales
- Sales by branch
- Sales by cashier
- Sales by payment method
- Product sales report
- IMEI sales report
- Discount report
- Void sales report
- Return/exchange report

### Inventory Reports
- Inventory summary
- Inventory ledger
- Stock movement report
- Stock adjustment report
- Transfer report
- Physical count report
- Low stock report
- Inventory valuation report
- Serialized inventory report

### Airtime Reports
- Airtime sales report
- Wallet balance report
- Wallet ledger report
- Wallet funding report
- Commission report
- Failed transaction report

### Financial Reports
- Cash flow report
- Expense report
- Expense category report
- Daily closing report
- Cash variance report
- Fund transfer report
- Financial ledger report

### Warranty Reports
- Warranty active report
- Warranty expiration report
- Warranty claims report
- Repair tracking report
- Replacement report

### Communication Reports
- Announcement engagement report
- Unread announcement report
- Chat activity report

### Audit & Security Reports
- User activity logs
- Login history
- Failed login attempts
- Permission changes
- Audit trail report

---

## 6. REPORT FILTER ENGINE

Build reusable report filter engine.

Supported filters:

- Date range
- Branch
- User
- Product
- Category
- Brand
- Provider
- Status
- Payment method
- Cashier
- Customer
- Warranty status
- Expense category

Rules:

- Filters must be reusable
- Reports must respect branch restrictions
- Reports must not expose unauthorized data
- Filters must be validated

---

## 7. REPORT EXPORT ENGINE

Build export system.

Supported exports:

- Excel
- PDF
- CSV if needed
- Print view

Rules:

- Exports must match filters
- Exports must include report title
- Exports must include generated date/time
- Exports must include branch context
- Export actions must be audit logged

---

## 8. PRINTABLE REPORTS

Create print-friendly layouts.

Requirements:

- Clean formatting
- Printable headers
- Branch information
- Date range
- Pagination
- Signature area if needed
- Totals and summaries

---

## 9. ANALYTICS COMPUTATION ENGINE

Build reusable analytics computation services.

Analytics examples:

- Total sales
- Gross sales
- Estimated net
- Inventory value
- Average transaction value
- Top products
- Top branches
- Product movement trend
- Expense ratio
- Commission totals

Rules:

- Computations must use actual data
- Avoid duplicate computations
- Optimize queries
- Prevent inaccurate aggregation

---

## 10. DASHBOARD FILTERS

Support dashboard filtering.

Filters:

- Branch
- Date range
- Product category
- Provider
- Expense category

Rules:

- Owner can filter all branches
- Branch users restricted to allowed branches
- Dashboard updates dynamically

---

## 11. KPI ALERTS

Create KPI alerts.

Alerts:

- Low sales performance
- High expenses
- High cash variance
- Low wallet balance
- High return rate
- Unusual inventory movement
- Expiring warranties
- Missing daily closing

Alerts must appear in:

- Executive dashboard
- Notification center

---

## 12. SCHEDULED REPORT PREPARATION

Prepare scheduled reporting structure.

Possible schedules:

- Daily
- Weekly
- Monthly

Examples:

- Daily sales summary
- Daily branch cash report
- Weekly inventory summary
- Monthly executive summary

Prepare architecture even if email scheduling is implemented later.

---

## 13. PERFORMANCE OPTIMIZATION

Optimize reporting queries.

Requirements:

- Pagination
- Lazy loading
- Query optimization
- Indexed filtering
- Cached summary widgets where safe
- Avoid N+1 queries

Large reports must remain usable.

---

# DATABASE REQUIREMENTS

Create or update migrations:

- report_exports
- dashboard_preferences
- analytics_snapshots optional
- scheduled_reports optional
- report_filters optional

Relationships:

- Report export belongs to user
- Dashboard preference belongs to user
- Scheduled report belongs to user

Avoid unnecessary duplication of transactional data.

Use existing modules as reporting source.

---

# BACKEND REQUIREMENTS

Create controllers:

- ExecutiveDashboardController
- BranchDashboardController
- SalesReportController
- InventoryReportController
- AirtimeReportController
- FinancialReportController
- WarrantyReportController
- CommunicationReportController
- AuditReportController
- ReportExportController

Create services:

- DashboardAnalyticsService
- SalesAnalyticsService
- InventoryAnalyticsService
- AirtimeAnalyticsService
- FinancialAnalyticsService
- WarrantyAnalyticsService
- ReportFilterService
- ReportExportService
- KPIAlertService

Business logic must be inside services.

Controllers must remain clean.

---

# UI/UX REQUIREMENTS

Create responsive screens:

- Executive dashboard
- Branch dashboard
- Report center
- Report detail pages
- Export modal
- Analytics summary cards
- KPI alert panel

UI requirements:

- Responsive charts
- Responsive tables
- Clear KPI cards
- Export buttons
- Filter sidebar/modal
- Mobile-friendly analytics layout
- Clean AdminLTE design
- Color-coded KPI indicators
- Branch selector
- Date picker

---

# SECURITY REQUIREMENTS

Implement permissions:

- view_executive_dashboard
- view_branch_dashboard
- view_sales_reports
- view_inventory_reports
- view_airtime_reports
- view_financial_reports
- view_warranty_reports
- view_communication_reports
- view_audit_reports
- export_reports
- manage_dashboard_preferences

Rules:

- Branch users can only access assigned branch reports
- Owner can access all reports
- Sensitive financial reports must be restricted
- Audit reports must be restricted
- Export permissions must be controlled
- Unauthorized users must not access analytics endpoints

---

# AUDIT TRAIL REQUIREMENTS

Log:

- Dashboard viewed
- Report generated
- Report exported
- Report printed
- Dashboard filter used
- KPI alert acknowledged
- Scheduled report created/updated

Audit must include:

- user_id
- branch_id nullable
- module_name
- action_type
- report_type nullable
- filters_used nullable
- ip_address
- user_agent
- created_at

---

# NOTIFICATION REQUIREMENTS

Generate notifications for:

- Low sales performance
- Missing daily closing
- High cash variance
- Critical low stock
- High expense spike
- Wallet balance critical
- Unresolved warranty claim
- Failed scheduled report if implemented

---

# VALIDATE SECTION

Validate:

## Dashboards
- Executive dashboard loads correctly
- Branch dashboard loads correctly
- Charts display correctly
- KPI cards compute correctly
- Branch filtering works

## Reports
- Sales reports are accurate
- Inventory reports are accurate
- Airtime reports are accurate
- Financial reports are accurate
- Warranty reports are accurate
- Audit reports are accurate

## Exports
- Excel export works
- PDF export works
- Print view works
- Export respects filters

## Analytics
- Totals are accurate
- Charts match actual data
- KPI alerts trigger correctly

## Security
- Branch restrictions work
- Unauthorized reports are blocked
- Export permissions work

## UI
- Dashboards are responsive
- Charts resize correctly
- Tables do not overflow
- Reports are mobile-friendly

---

# FIX SECTION

If issues are found:

- Fix report computations
- Fix chart data mismatch
- Fix export formatting
- Fix branch filtering
- Fix unauthorized access
- Fix dashboard loading performance
- Fix slow queries
- Fix KPI computation
- Fix mobile responsiveness
- Refactor duplicated analytics logic

Revalidate after fixing.

---

# GATEWAY REVIEW SECTION

Before marking Phase 8 complete, verify:

- global_master.md is followed
- executive dashboard is complete
- branch dashboard is complete
- reports are accurate
- exports work correctly
- analytics computations are accurate
- KPI alerts work
- branch restrictions are enforced
- audit logs are complete
- no incorrect aggregation
- no unauthorized report access
- no broken charts
- no slow unusable reports
- no broken responsive UI
- no route conflict
- no migration conflict

---

# EXPECTED OUTPUT

At the end of Phase 8, provide:

- Complete executive dashboard
- Complete branch dashboard
- Complete report center
- Complete sales reports
- Complete inventory reports
- Complete airtime reports
- Complete financial reports
- Complete warranty reports
- Complete communication reports
- Complete audit reports
- Complete analytics engine
- Complete report export engine
- Complete KPI alerts
- Scheduled report preparation
- Updated migrations
- Updated models
- Updated controllers
- Updated services
- Updated routes
- Updated views
- Updated permissions
- Updated audit logs
- Updated notifications
- Responsive analytics and reports UI

PHASE 8 IS NOT COMPLETE UNTIL ALL VALIDATIONS PASS.
```
