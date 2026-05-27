````markdown
# RC STORE RMS — PHASE 16
# AI ANALYTICS & FORECASTING
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
DO NOT INVENT DATA.
DO NOT MAKE UNSUPPORTED AI CLAIMS.

---

# PHASE OBJECTIVE

Build the complete AI Analytics and Forecasting Module for RC Store RMS.

This optional phase must provide intelligent business insights based only on actual system data.

The module must support:

- Sales forecasting
- Inventory demand forecasting
- Smart reorder suggestions
- Fast-moving product detection
- Slow-moving product detection
- Dead stock identification
- Branch performance insights
- Expense trend analysis
- Airtime trend analysis
- Customer purchasing insights
- AI-generated alerts
- Explainable recommendations

This phase depends on:

- Phase 2 — Product & Inventory Management
- Phase 3 — POS & Sales Management
- Phase 4 — Digital Load / Airtime Management
- Phase 5 — Cash Flow, Expenses & Daily Closing
- Phase 6 — Warranty & Customer Management
- Phase 8 — Reports, Analytics & Executive Dashboard
- Phase 9 — Supplier, Purchasing & Office Supplies Management

---

# BUILD SECTION

## 1. AI ANALYTICS DASHBOARD

Create AI analytics dashboard.

Dashboard cards:

- Sales forecast
- Inventory demand forecast
- Suggested reorder items
- Fast-moving products
- Slow-moving products
- Dead stock items
- Branch performance alerts
- Expense trend alerts
- Airtime wallet forecast
- Customer behavior insights

Rules:

- Use only actual system data
- Do not invent analytics
- Show “insufficient data” when data is not enough
- Mark confidence level if applicable
- Explain why each recommendation was generated

---

## 2. SALES FORECASTING

Build sales forecasting.

Forecast by:

- Branch
- Product
- Category
- Brand
- Date range
- Sales trend
- Seasonality preparation
- Payment method if useful

Sales forecast output:

- Expected sales amount
- Expected quantity sold
- Trend direction
- Confidence level
- Explanation
- Data period used

Rules:

- Forecast must use historical sales data
- If data is insufficient, show clear message
- Forecast must not be treated as guaranteed result
- Forecast must be explainable

---

## 3. INVENTORY DEMAND FORECASTING

Build inventory demand forecasting.

Analyze:

- Stock movement
- Sales velocity
- Product demand
- Branch demand
- Stock-out history
- Reorder level
- Lead time if configured

Output:

- Expected demand
- Suggested reorder quantity
- Expected stock-out date
- Demand trend
- Confidence level
- Explanation

Rules:

- Do not create automatic purchase orders
- Reorder suggestions must require management review
- Forecast must respect branch inventory
- Forecast must be based on real data

---

## 4. SMART REORDER SUGGESTIONS

Build reorder recommendation engine.

Suggested reorder must consider:

- Current stock
- Average daily sales
- Reorder level
- Safety stock
- Supplier lead time if available
- Branch demand
- Recent sales trend
- Pending purchase orders

Output:

- Product
- Branch
- Current stock
- Average daily sales
- Suggested reorder quantity
- Urgency level
- Reason

Urgency levels:

- Low
- Medium
- High
- Critical

Rules:

- Suggestions are advisory only
- Purchase order creation must still require approval
- No automatic purchasing without user action

---

## 5. FAST-MOVING PRODUCT DETECTION

Detect fast-moving products.

Criteria may include:

- High sales quantity
- High sales frequency
- Fast inventory turnover
- Consistent demand

Output:

- Product
- Branch
- Quantity sold
- Sales value
- Turnover rate
- Recommendation

---

## 6. SLOW-MOVING PRODUCT DETECTION

Detect slow-moving products.

Criteria may include:

- Low sales quantity
- Long shelf time
- Low movement frequency
- High remaining inventory

Output:

- Product
- Branch
- Days without sale
- Stock quantity
- Inventory value
- Suggested action

Suggested actions:

- Promote
- Discount
- Transfer to another branch
- Monitor
- Stop reorder

---

## 7. DEAD STOCK IDENTIFICATION

Identify dead stock.

Dead stock rules must be configurable.

Example criteria:

- No sales for configurable number of days
- Stock remains above threshold
- No inventory movement

Output:

- Product
- Branch
- Days inactive
- Quantity
- Value
- Suggested action

---

## 8. BRANCH PERFORMANCE INSIGHTS

Analyze branch performance.

Metrics:

- Sales growth
- Expense-to-sales ratio
- Cash variance pattern
- Inventory movement
- Airtime sales
- Warranty claim rate
- Daily closing compliance

Output:

- Best-performing branch
- Underperforming branch
- Branch risk indicators
- Improvement suggestions

Rules:

- Insights must explain basis
- Do not blame users
- Use neutral business language

---

## 9. EXPENSE TREND ANALYSIS

Analyze expenses.

Detect:

- High expense category
- Expense spike
- Branch expense increase
- Unusual expense pattern
- Expense-to-sales ratio

Output:

- Expense insight
- Branch affected
- Category affected
- Amount trend
- Suggested review action

---

## 10. AIRTIME TREND ANALYSIS

Analyze airtime transactions.

Metrics:

- Provider sales trend
- Wallet balance trend
- Wallet funding frequency
- Commission trend
- Failed/reversed transaction pattern

Output:

- Provider performance
- Wallet funding prediction
- Low wallet forecast
- Commission insights
- Suspicious trend alerts

---

## 11. CUSTOMER PURCHASING INSIGHTS

Analyze customer behavior.

Insights:

- Frequent customers
- High-value customers
- Repeat buyers
- Product preferences
- Warranty claim tendency
- Promo responsiveness if Phase 13 exists

Rules:

- Protect customer data
- Do not expose sensitive customer information to unauthorized users
- Insights must respect permissions

---

## 12. AI ALERTS

Generate AI alerts.

Alert types:

- Product likely to run out soon
- Unusual sales drop
- High expense spike
- Repeated cash variance
- Slow-moving inventory
- Dead stock detected
- Branch performance decline
- Airtime wallet likely to run low
- Customer purchase trend change

Alerts must appear in:

- AI dashboard
- Executive dashboard
- Notification center

---

## 13. AI INSIGHT EXPLANATION

Every AI insight must include:

- Insight title
- Affected branch
- Affected product/category if applicable
- Data period used
- Observation
- Recommendation
- Confidence level
- Limitation note if applicable

Example explanation:

```text
Product X is likely to run out within 5 days based on average daily sales of 8 units and current available stock of 40 units.
```

---

## 14. AI REPORTS

Create AI reports:

- Sales forecast report
- Inventory demand forecast report
- Smart reorder report
- Fast-moving product report
- Slow-moving product report
- Dead stock report
- Branch performance insight report
- Expense trend report
- Airtime trend report
- Customer behavior report

Reports must support:

- Date filter
- Branch filter
- Product filter
- Category filter
- Export to Excel
- Export to PDF
- Print view

---

# DATABASE REQUIREMENTS

Create or update migrations:

- analytics_models
- ai_insights
- forecast_results
- reorder_suggestions
- inventory_demand_predictions
- branch_performance_insights
- customer_behavior_insights
- ai_alerts
- ai_report_exports

Rules:

- Do not duplicate transactional data unnecessarily
- Use existing sales, inventory, finance, airtime, warranty, and customer data as sources
- Store generated insights only when needed for tracking/history

---

# BACKEND REQUIREMENTS

Create controllers:

- AIAnalyticsDashboardController
- SalesForecastController
- InventoryForecastController
- ReorderSuggestionController
- ProductMovementInsightController
- BranchPerformanceInsightController
- ExpenseInsightController
- AirtimeInsightController
- CustomerInsightController
- AIAlertController
- AIReportController

Create services:

- AIAnalyticsService
- SalesForecastService
- InventoryForecastService
- ReorderSuggestionService
- ProductMovementInsightService
- BranchPerformanceInsightService
- ExpenseTrendInsightService
- AirtimeTrendInsightService
- CustomerBehaviorInsightService
- AIAlertService
- AIReportService

Business logic must be inside services.

Controllers must remain clean.

---

# AI / ANALYTICS RULES

STRICTLY FOLLOW:

- Use only actual system data
- Do not invent records
- Do not invent sales
- Do not invent customers
- Do not invent branch performance
- Show insufficient data when applicable
- Mark low-confidence results
- Explain every recommendation
- Do not make automatic business decisions
- Do not automatically create PO without approval
- Do not automatically change inventory
- Do not automatically adjust prices
- Do not automatically contact customers without approval

---

# UI/UX REQUIREMENTS

Create responsive screens:

- AI analytics dashboard
- Sales forecast page
- Inventory forecast page
- Reorder suggestion page
- Fast-moving products page
- Slow-moving products page
- Dead stock page
- Branch insight page
- Expense insight page
- Airtime insight page
- Customer insight page
- AI alerts page
- AI reports page

UI requirements:

- Insight cards
- Confidence indicators
- Trend arrows
- Recommendation badges
- Branch filters
- Date filters
- Product filters
- Responsive charts
- Mobile-friendly tables
- Clear explanation section
- “Insufficient data” state

---

# SECURITY REQUIREMENTS

Implement permissions:

- view_ai_dashboard
- view_sales_forecast
- view_inventory_forecast
- view_reorder_suggestions
- view_product_movement_insights
- view_branch_insights
- view_expense_insights
- view_airtime_insights
- view_customer_insights
- view_ai_alerts
- view_ai_reports
- export_ai_reports

Rules:

- Branch users can only view assigned branch insights
- Owner can view all branches
- Customer insights must be restricted
- AI reports must respect branch permissions
- Recommendations must not bypass approval workflow
- Sensitive data must not be exposed

---

# AUDIT TRAIL REQUIREMENTS

Log:

- AI dashboard viewed
- Forecast generated
- Reorder suggestions generated
- AI insight viewed
- AI report exported
- AI alert acknowledged
- AI configuration updated if applicable

Audit must include:

- user_id
- branch_id nullable
- module_name
- action_type
- filter_used nullable
- ip_address
- user_agent
- created_at

---

# NOTIFICATION REQUIREMENTS

Generate notifications for:

- Critical reorder suggestion
- Product likely to run out
- High expense spike
- Unusual sales drop
- Repeated cash variance
- Dead stock detected
- Low wallet forecast
- Branch performance concern

---

# VALIDATE SECTION

Validate:

## Sales Forecast
- Uses actual sales data
- Handles insufficient data
- Outputs understandable forecast
- Does not invent results

## Inventory Forecast
- Uses inventory movement
- Computes stock-out estimate
- Generates reorder suggestion correctly
- Does not create automatic PO

## Product Movement
- Fast-moving detection works
- Slow-moving detection works
- Dead stock detection works

## Branch Insights
- Branch performance computation works
- Branch filtering works
- Owner vs branch access works

## Expense Insights
- Expense spikes are detected correctly
- Expense ratios are accurate

## Airtime Insights
- Wallet prediction works
- Provider trend is accurate
- Failed/reversed transaction trends are accurate

## Customer Insights
- Customer data is protected
- Only authorized users can access insights

## Reports
- Filters work
- Export works
- Print view works

## UI
- AI pages are responsive
- Charts resize correctly
- Tables do not overflow
- Insight explanations are clear

---

# FIX SECTION

If issues are found:

- Fix forecast computation
- Fix reorder logic
- Fix stock-out estimate
- Fix product movement detection
- Fix expense trend calculation
- Fix branch filtering
- Fix customer data exposure
- Fix permission leaks
- Fix report inaccuracies
- Fix insufficient data handling
- Fix misleading insight labels
- Fix responsive issues
- Refactor duplicated analytics logic

Revalidate after fixing.

---

# GATEWAY REVIEW SECTION

Before marking Phase 16 complete, verify:

- global_master.md is followed
- AI dashboard is complete
- sales forecasting works
- inventory forecasting works
- smart reorder suggestions work
- fast-moving detection works
- slow-moving detection works
- dead stock detection works
- branch insights work
- expense insights work
- airtime insights work
- customer insights are protected
- AI alerts work
- reports work
- branch restrictions are enforced
- audit logs are complete
- no invented data
- no unsupported conclusions
- no automatic unauthorized action
- no customer data exposure
- no broken responsive UI
- no route conflict
- no migration conflict

---

# EXPECTED OUTPUT

At the end of Phase 16, provide:

- Complete AI analytics dashboard
- Complete sales forecasting
- Complete inventory demand forecasting
- Complete smart reorder suggestions
- Complete fast-moving product detection
- Complete slow-moving product detection
- Complete dead stock detection
- Complete branch performance insights
- Complete expense trend insights
- Complete airtime trend insights
- Complete customer purchasing insights
- Complete AI alerts
- Complete AI reports
- Explainable recommendation structure
- Insufficient data handling
- Updated migrations
- Updated models
- Updated controllers
- Updated services
- Updated routes
- Updated views
- Updated permissions
- Updated audit logs
- Updated notifications
- Responsive AI analytics UI

PHASE 16 IS NOT COMPLETE UNTIL ALL VALIDATIONS PASS.
````
