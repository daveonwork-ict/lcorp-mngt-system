````markdown
# RC STORE RMS — PHASE 13
# LOYALTY, PROMOTIONS & CUSTOMER ENGAGEMENT
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
DO NOT IGNORE CUSTOMER DATA PRIVACY.
DO NOT IGNORE SALES INTEGRATION.

---

# PHASE OBJECTIVE

Build the complete Loyalty, Promotions, and Customer Engagement Module for RC Store RMS.

This phase aims to improve customer retention, repeat purchases, customer engagement, promotional marketing, and sales growth.

The module must support:

- Loyalty points
- Membership levels
- Promo campaigns
- Discount campaigns
- Product bundle promotions
- Customer rewards
- Referral rewards
- Birthday rewards
- SMS/Email notification readiness
- Customer engagement analytics

This phase depends on:

- Phase 3 — POS & Sales Management
- Phase 6 — Warranty & Customer Management
- Phase 7 — Communication Module
- Phase 8 — Reports & Analytics

---

# BUILD SECTION

## 1. LOYALTY PROGRAM MANAGEMENT

Create loyalty program setup.

Fields:

- program_code
- program_name
- description
- points_conversion_rule
- minimum_purchase_amount nullable
- points_expiration_days nullable
- status

Features:

- Create loyalty program
- Edit loyalty program
- Activate/deactivate loyalty program
- Configure points rules
- Configure expiration rules

Rules:

- Loyalty rules must be database-driven
- Do not hardcode points computation
- Only active loyalty programs can be used

---

## 2. CUSTOMER LOYALTY ACCOUNT

Create customer loyalty account.

Fields:

- customer_id
- loyalty_program_id
- loyalty_number
- total_points_earned
- total_points_redeemed
- current_points_balance
- membership_level_id nullable
- status

Statuses:

- Active
- Suspended
- Expired
- Blocked

Rules:

- One active loyalty account per customer per program
- Points balance cannot become negative
- Loyalty account must link to actual customer

---

## 3. LOYALTY POINTS COMPUTATION

Create points earning rules.

Possible computation:

- Fixed points
- Percentage-based points
- Product category-based points
- Promo-based bonus points
- Branch-based rules

Process flow:

```text
Customer Purchases Product
→ POS Validates Loyalty Eligibility
→ System Computes Points
→ Loyalty Points Added
→ Loyalty Ledger Created
→ Customer Balance Updated
→ Notification Prepared
```

Rules:

- Voided/refunded sale must reverse points
- Duplicate point posting must be prevented
- Points computation must be traceable

---

## 4. LOYALTY LEDGER

Create loyalty transaction ledger.

Ledger types:

- Earned
- Redeemed
- Reversed
- Expired
- Adjusted
- Bonus

Fields:

- loyalty_account_id
- customer_id
- sale_id nullable
- transaction_type
- points
- running_balance
- remarks
- created_by
- created_at

Rules:

- Every points movement must create ledger entry
- Ledger must not be editable directly
- Adjustments require permission

---

## 5. LOYALTY POINT REDEMPTION

Create points redemption workflow.

Redemption options:

- Discount
- Free item
- Voucher
- Cashback preparation
- Promo reward

Process flow:

```text
Customer Redeems Points
→ System Validates Balance
→ System Computes Equivalent Reward
→ Staff Confirms Redemption
→ Points Deducted
→ Redemption Recorded
→ POS Discount/Reward Applied
→ Loyalty Ledger Updated
```

Rules:

- Redemption cannot exceed balance
- Redemption must be linked to sale if applicable
- Redemption must be audit logged
- Unauthorized redemption is prohibited

---

## 6. MEMBERSHIP LEVEL MANAGEMENT

Create membership level setup.

Suggested levels:

- Bronze
- Silver
- Gold
- Platinum
- VIP

Fields:

- level_name
- minimum_points
- discount_percentage nullable
- priority_support
- birthday_reward nullable
- special_benefits
- status

Rules:

- Membership level must auto-update if configured
- Level rules must be configurable

---

## 7. PROMOTION MANAGEMENT

Create promotion engine.

Promotion types:

- Product discount
- Percentage discount
- Buy 1 Take 1
- Bundle promo
- Freebie promo
- Flash sale
- Branch promo
- Category promo
- Brand promo
- Loyalty bonus promo

Fields:

- promo_code
- promo_name
- promo_type
- start_date
- end_date
- branch_scope
- applicable_products
- applicable_categories
- applicable_brands
- minimum_purchase
- promo_value
- status

Rules:

- Promotions must be database-driven
- Expired promotions must not apply
- Promo conflicts must be validated
- Multiple promo stacking must follow rules

---

## 8. PROMO APPLICATION ENGINE

Build promo application logic.

Process flow:

```text
POS Transaction Started
→ System Checks Eligible Promotions
→ System Computes Promo
→ Staff Reviews Promo
→ Promo Applied
→ Final Total Computed
→ Promo Usage Logged
```

Rules:

- Promo must validate branch scope
- Promo must validate date
- Promo must validate eligibility
- Promo application must be logged

---

## 9. BUNDLE PROMOTION MANAGEMENT

Support product bundles.

Examples:

- Phone + Case Bundle
- Beauty Package Bundle
- Gadget Accessories Bundle

Bundle fields:

- bundle_code
- bundle_name
- bundle_price
- included_products
- start_date
- end_date
- status

Rules:

- Bundle stock validation required
- Bundle pricing must compute correctly
- Bundle sales must deduct correct inventory

---

## 10. REFERRAL REWARD SYSTEM

Create referral system.

Fields:

- referrer_customer_id
- referred_customer_id
- referral_code
- reward_type
- reward_value
- referral_status

Statuses:

- Pending
- Qualified
- Rewarded
- Cancelled

Rules:

- Referral must not self-reference
- Reward only after qualified transaction
- Duplicate referrals prevented

---

## 11. BIRTHDAY REWARD SYSTEM

Create birthday reward readiness.

Features:

- Birthday discount
- Birthday points bonus
- Birthday voucher

Rules:

- Reward only during valid birthday window
- One reward per cycle
- Reward must be logged

---

## 12. CUSTOMER ENGAGEMENT CAMPAIGNS

Create campaign management.

Campaign types:

- Promo campaign
- Loyalty campaign
- Seasonal campaign
- Clearance sale
- Anniversary promo
- Branch event

Fields:

- campaign_name
- campaign_type
- start_date
- end_date
- target_customers
- message_content
- status

Features:

- Campaign monitoring
- Campaign analytics
- Campaign targeting

---

## 13. SMS / EMAIL READINESS

Prepare customer notification readiness.

Possible notifications:

- Loyalty earned
- Loyalty redeemed
- Promo alerts
- Birthday rewards
- Referral rewards
- Membership upgrade

Prepare architecture only if actual provider not yet integrated.

---

## 14. CUSTOMER ENGAGEMENT DASHBOARD

Create customer engagement dashboard.

Cards:

- Active loyalty members
- Points earned today
- Points redeemed today
- Active promos
- Top customers
- Membership upgrades
- Referral rewards
- Birthday rewards issued

Charts:

- Loyalty trend
- Promo usage trend
- Top promo performance
- Customer retention trend

Tables:

- Recent loyalty transactions
- Active promotions
- Top customers
- Recent redemptions

---

## 15. LOYALTY & PROMOTION REPORTS

Create reports:

- Loyalty points report
- Loyalty ledger report
- Redemption report
- Membership level report
- Promotion usage report
- Promo performance report
- Referral reward report
- Customer engagement report

Reports must support:

- Date filter
- Branch filter
- Customer filter
- Promo filter
- Membership filter
- Excel export
- PDF export
- Print view

---

# DATABASE REQUIREMENTS

Create or update migrations:

- loyalty_programs
- loyalty_accounts
- loyalty_ledgers
- membership_levels
- promotions
- promotion_logs
- product_bundles
- referral_rewards
- birthday_rewards
- engagement_campaigns
- customer_notification_logs

Relationships:

- Customer has loyalty account
- Loyalty account belongs to program
- Loyalty account has many ledger entries
- Membership level belongs to loyalty account
- Promotion has many usage logs
- Bundle has many products
- Referral reward belongs to customer
- Campaign may target customers

---

# BACKEND REQUIREMENTS

Create controllers:

- LoyaltyProgramController
- LoyaltyAccountController
- LoyaltyLedgerController
- MembershipLevelController
- PromotionController
- BundlePromotionController
- ReferralRewardController
- BirthdayRewardController
- CampaignController
- EngagementDashboardController
- LoyaltyReportController

Create services:

- LoyaltyService
- LoyaltyPointsService
- LoyaltyRedemptionService
- MembershipLevelService
- PromotionEngineService
- BundleService
- ReferralRewardService
- BirthdayRewardService
- CampaignService
- CustomerEngagementService

Business logic must be inside services.

Controllers must remain clean.

---

# UI/UX REQUIREMENTS

Create responsive screens:

- Loyalty dashboard
- Loyalty program setup
- Loyalty account page
- Loyalty ledger page
- Membership level page
- Promotion list
- Promotion form
- Bundle management page
- Referral reward page
- Campaign page
- Customer engagement dashboard
- Loyalty reports page

UI requirements:

- Promo banners/cards
- Loyalty status badges
- Membership level indicators
- Responsive tables
- Mobile-friendly forms
- Customer summary cards
- Points balance display
- Promo validity indicators

---

# SECURITY REQUIREMENTS

Implement permissions:

- manage_loyalty_programs
- view_loyalty_accounts
- adjust_loyalty_points
- redeem_loyalty_points
- manage_promotions
- manage_bundles
- manage_referral_rewards
- manage_campaigns
- view_customer_engagement_dashboard
- view_loyalty_reports
- export_loyalty_reports

Rules:

- Loyalty adjustments are restricted
- Promo management is restricted
- Branch restrictions must apply where applicable
- Customer data must be protected
- Unauthorized redemption prohibited

---

# AUDIT TRAIL REQUIREMENTS

Log:

- Loyalty program created/updated
- Loyalty points earned
- Loyalty points redeemed
- Loyalty points adjusted
- Membership level changed
- Promotion created/updated
- Promo applied
- Bundle sold
- Referral reward issued
- Birthday reward issued
- Campaign created
- Report exported

Audit must include:

- user_id
- branch_id nullable
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

- Loyalty points earned
- Loyalty points redeemed
- Membership level upgraded
- Promo started
- Promo ending soon
- Birthday reward available
- Referral reward issued

---

# VALIDATE SECTION

Validate:

## Loyalty
- Loyalty account works
- Points computation works
- Points reversal works
- Redemption works
- Ledger accuracy works

## Membership
- Membership levels update correctly
- Benefits apply correctly

## Promotions
- Promotions apply correctly
- Date validation works
- Branch validation works
- Bundle pricing works
- Promo stacking rules work

## Referrals
- Referral rewards work
- Duplicate referral prevention works

## Reports
- Reports are accurate
- Exports work
- Filters work

## Security
- Permissions work
- Customer data protected
- Unauthorized redemption blocked

## UI
- Loyalty pages are responsive
- Promo pages are responsive
- Mobile forms work
- Tables do not overflow

---

# FIX SECTION

If issues are found:

- Fix loyalty computation
- Fix promo application logic
- Fix bundle inventory deduction
- Fix membership update logic
- Fix referral duplication
- Fix points reversal
- Fix branch restriction leaks
- Fix permission leaks
- Fix report inaccuracies
- Fix responsive issues
- Refactor duplicated loyalty logic

Revalidate after fixing.

---

# GATEWAY REVIEW SECTION

Before marking Phase 13 complete, verify:

- global_master.md is followed
- loyalty module is complete
- points computation works
- redemption works
- membership levels work
- promotion engine works
- bundle promotions work
- referral rewards work
- birthday rewards work
- engagement dashboard works
- reports work
- branch restrictions are enforced
- audit logs are complete
- no duplicate points posting
- no unauthorized redemption
- no invalid promotion stacking
- no customer data exposure
- no broken responsive UI
- no route conflict
- no migration conflict

---

# EXPECTED OUTPUT

At the end of Phase 13, provide:

- Complete loyalty program module
- Complete loyalty account management
- Complete loyalty ledger
- Complete loyalty redemption
- Complete membership level system
- Complete promotion engine
- Complete bundle promotions
- Complete referral rewards
- Complete birthday rewards
- Complete customer engagement campaigns
- Complete customer engagement dashboard
- Complete loyalty and promo reports
- SMS/email notification readiness
- Updated migrations
- Updated models
- Updated controllers
- Updated services
- Updated routes
- Updated views
- Updated permissions
- Updated audit logs
- Updated notifications
- Responsive loyalty and promotion UI

PHASE 13 IS NOT COMPLETE UNTIL ALL VALIDATIONS PASS.
````
