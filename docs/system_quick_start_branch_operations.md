# RC Store RMS Quick Start (Branch Operations)

## Purpose

This quick-start is a practical, shift-based guide for branch teams.
Use this for daily execution. For full policies and deep workflows, see:

- docs/system_user_guide_comprehensive.md

## Who Uses This

- Branch Manager
- Cashier
- Inventory Staff
- Airtime Staff

## 1) Start of Day Checklist (5-10 minutes)

1. Login at `/login`.
2. Confirm you are on the correct branch context.
3. Open cash float at `/cash-flow/openings`.
4. Check pending approvals at `/approvals`.
5. Check critical alerts:
- Inventory alerts: `/inventory/alerts`
- Airtime reports and wallet status: `/airtime/reports`

Success state:

- Branch is selected correctly.
- Opening cash is posted.
- No blocked approval items for today.

## 2) Frontline Sales Flow (Cashier)

Main screen: `/pos`

1. Add items to cart.
2. Confirm price/quantity before checkout.
3. Complete payment and post sale.
4. Reprint receipt if needed from `/sales/{sale}/receipt/reprint`.

When customer pauses checkout:

- Use held transactions at `/sales/held-transactions`.

When correction is needed after posting:

- File void request from `/sales/void-requests`.
- File return from `/sales/returns`.

## 3) Airtime Flow (Airtime Staff)

Main screen: `/airtime/transactions`

1. Select wallet/provider.
2. Process load transaction.
3. Verify posted result in transaction list.

If wallet is low:

- Request funding at `/airtime/fundings`.

If a correction is needed:

- File wallet adjustment at `/airtime/adjustments`.
- Reverse incorrect load via `/airtime/transactions/{transaction}`.

## 4) Inventory Control Flow (Inventory Staff)

1. Receive goods through stock-in at `/inventory/stock-ins`.
2. Monitor branch stock at `/inventory/branch-inventory`.
3. Submit inventory adjustment if discrepancy exists at `/inventory/adjustments`.
4. Process transfer requests at `/inventory/transfers`.

Best practice:

- Post receiving and adjustments the same day to keep POS stock accurate.

## 5) Branch Manager Midday Check (10 minutes)

1. Review branch performance at `/dashboard/branch`.
2. Clear approvals queue at `/approvals`.
3. Check expenses status at `/expenses`.
4. Confirm no unresolved variances at `/cash-flow/variances`.

## 6) End of Day Closing Checklist (15-20 minutes)

1. Confirm all pending POS holds are resolved.
2. Finalize expense submissions and decisions.
3. Run daily closing at `/cash-flow/daily-closing`.
4. Validate variance list at `/cash-flow/variances`.
5. Review branch totals in reports:
- Cash flow reports: `/cash-flow/reports`
- Sales reports: `/reports/sales`

Success state:

- Daily closing posted.
- Variances explained or escalated.
- Daily reports ready for manager review.

## 7) Quick Escalation Guide

Escalate to Branch Manager when:

- Approval is blocked or unclear.
- Customer dispute requires return/void decision.
- Daily closing variance cannot be reconciled.

Escalate to Admin/IT when:

- Access denied due to permission mismatch.
- Session/account issues persist after relogin.
- Report export or print fails repeatedly.

## 8) Common Issues and Fast Fixes

Issue: Access denied / forbidden

1. Confirm correct account role.
2. Confirm branch assignment.
3. Retry after relogin.
4. Escalate if still blocked.

Issue: Transaction not visible

1. Check branch filter.
2. Check date/status filters.
3. Verify you are in the correct module list page.

Issue: Cannot approve request

1. Confirm request status is pending.
2. Confirm your permission includes approval action.
3. Confirm branch scope matches request branch.

## 9) Daily Route Shortcuts

- Branch dashboard: `/dashboard/branch`
- POS: `/pos`
- Sales list: `/sales`
- Held transactions: `/sales/held-transactions`
- Airtime transactions: `/airtime/transactions`
- Airtime reports: `/airtime/reports`
- Inventory dashboard: `/inventory/dashboard`
- Inventory stock-ins: `/inventory/stock-ins`
- Expenses: `/expenses`
- Approvals inbox: `/approvals`
- Daily closing: `/cash-flow/daily-closing`
- Reports hub: `/reports`

## 10) Control Rules (Non-Negotiable)

- Do not share accounts.
- Do not bypass approval flows.
- Do not leave unresolved variances overnight without manager note.
- Always verify branch context before posting transactions.
