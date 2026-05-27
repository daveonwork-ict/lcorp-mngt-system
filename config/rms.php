<?php

return [
    'owner_role_code' => env('RMS_OWNER_ROLE_CODE', 'owner'),

    'default_roles' => [
        ['code' => 'owner', 'name' => 'Owner', 'description' => 'Executive access across all modules'],
        ['code' => 'manager', 'name' => 'Branch Manager', 'description' => 'Branch operations and approvals'],
        ['code' => 'cashier', 'name' => 'Cashier', 'description' => 'POS and customer-facing transactions'],
    ],

    'default_settings' => [
        ['key' => 'system.company_name', 'value' => 'RC Store RMS', 'group' => 'branding'],
        ['key' => 'system.currency', 'value' => 'PHP', 'group' => 'regional'],
        ['key' => 'security.session_timeout_minutes', 'value' => '120', 'group' => 'security'],
    ],

    'modules' => [
        ['slug' => 'pos', 'name' => 'POS', 'route' => 'pos.index', 'permission' => 'pos.view', 'screens' => ['Touchscreen POS', 'Receipt Preview', 'Held Transactions']],
        ['slug' => 'inventory', 'name' => 'Inventory', 'route' => 'inventory.index', 'permission' => 'inventory.view', 'screens' => ['Product Masterlist', 'Stock Transfer', 'Movement Ledger', 'Low Stock']],
        ['slug' => 'airtime', 'name' => 'Airtime / Digital Load', 'route' => 'airtime.index', 'permission' => 'airtime.view', 'screens' => ['Wallet Balance', 'Funding', 'Load Transactions', 'Airtime Reports']],
        ['slug' => 'cash-flow', 'name' => 'Cash Flow', 'route' => 'cash-flow.index', 'permission' => 'cash-flow.view', 'screens' => ['Opening Cash', 'Daily Closing', 'Cash Variance']],
        ['slug' => 'expenses', 'name' => 'Expenses', 'route' => 'expenses.index', 'permission' => 'expenses.view', 'screens' => ['Expense Encoding', 'Expense Categories', 'Approvals']],
        ['slug' => 'warranty', 'name' => 'Warranty', 'route' => 'warranty.index', 'permission' => 'warranty.view', 'screens' => ['Warranty Lookup', 'Claim Form', 'Status Timeline']],
        ['slug' => 'customers', 'name' => 'Customers', 'route' => 'customers.index', 'permission' => 'customers.view', 'screens' => ['Customer List', 'Customer Profile', 'Purchase History']],
        ['slug' => 'suppliers', 'name' => 'Suppliers', 'route' => 'suppliers.index', 'permission' => 'suppliers.view', 'screens' => ['Supplier Registry', 'Supplier Performance']],
        ['slug' => 'purchasing', 'name' => 'Purchasing', 'route' => 'purchasing.index', 'permission' => 'purchasing.view', 'screens' => ['Purchase Orders', 'Receiving']],
        ['slug' => 'office-supplies', 'name' => 'Office Supplies', 'route' => 'office-supplies.index', 'permission' => 'office-supplies.view', 'screens' => ['Supplies Catalog', 'Issuance Logs']],
        ['slug' => 'announcements', 'name' => 'Announcements', 'route' => 'announcements.index', 'permission' => 'announcements.view', 'screens' => ['Announcement Feed', 'Read Tracking']],
        ['slug' => 'chat', 'name' => 'Chat', 'route' => 'chat.index', 'permission' => 'chat.view', 'screens' => ['Room List', 'Conversation Panel', 'Notifications']],
        ['slug' => 'reports', 'name' => 'Reports', 'route' => 'reports.index', 'permission' => 'reports.view', 'screens' => ['Sales', 'Inventory', 'Airtime', 'Expenses', 'Warranty', 'Branch']],
        ['slug' => 'approvals', 'name' => 'Approvals', 'route' => 'approvals.index', 'permission' => 'approvals.view', 'screens' => ['Pending Requests', 'Approval Timeline']],
        ['slug' => 'audit-trail', 'name' => 'Audit Trail', 'route' => 'audit-trail.index', 'permission' => 'audit-trail.view', 'screens' => ['Action Ledger', 'IP Tracking']],
        ['slug' => 'users-roles', 'name' => 'Users & Roles', 'route' => 'users-roles.index', 'permission' => 'users-roles.view', 'screens' => ['User Directory', 'Role Matrix', 'Permission Matrix']],
        ['slug' => 'branches', 'name' => 'Branches', 'route' => 'branches.index', 'permission' => 'branches.view', 'screens' => ['Branch List', 'Branch Profile']],
        ['slug' => 'settings', 'name' => 'Settings', 'route' => 'settings.index', 'permission' => 'settings.view', 'screens' => ['System Settings', 'Security Settings']],
    ],

    'owner_metrics' => [
        ['label' => "Today's Sales", 'value' => 'PHP 124,500', 'trend' => '+12.4%'],
        ['label' => 'Monthly Sales', 'value' => 'PHP 2.18M', 'trend' => '+8.2%'],
        ['label' => 'Total Expenses', 'value' => 'PHP 713,440', 'trend' => '-3.1%'],
        ['label' => 'Net Income', 'value' => 'PHP 1.46M', 'trend' => '+10.1%'],
        ['label' => 'Cash Position', 'value' => 'PHP 512,200', 'trend' => '+5.0%'],
        ['label' => 'Inventory Value', 'value' => 'PHP 3.06M', 'trend' => '+1.4%'],
        ['label' => 'Low Stock Items', 'value' => '37', 'trend' => 'Needs action'],
        ['label' => 'Airtime Wallet Balance', 'value' => 'PHP 84,300', 'trend' => '+2.9%'],
        ['label' => 'Pending Approvals', 'value' => '11', 'trend' => 'Urgent'],
        ['label' => 'Warranty Claims', 'value' => '9', 'trend' => '+1'],
        ['label' => 'Unread Announcements', 'value' => '4', 'trend' => 'Review'],
        ['label' => 'Active Branches', 'value' => '6 / 6', 'trend' => 'Stable'],
    ],

    'owner_charts' => [
        'sales_per_branch' => ['labels' => ['Main', 'North', 'South', 'West', 'Online'], 'values' => [450, 320, 270, 300, 420]],
        'sales_trend' => ['labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'], 'values' => [120, 128, 140, 132, 152, 188, 205]],
        'expense_trend' => ['labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'], 'values' => [40, 38, 42, 36, 48, 44, 50]],
        'inventory_by_branch' => ['labels' => ['Main', 'North', 'South', 'West', 'Online'], 'values' => [820, 640, 590, 610, 700]],
        'airtime_provider_sales' => ['labels' => ['Provider A', 'Provider B', 'Provider C'], 'values' => [52, 33, 21]],
        'branch_ranking' => ['labels' => ['Main', 'Online', 'North', 'West', 'South'], 'values' => [1, 2, 3, 4, 5]],
    ],

    'owner_tables' => [
        'recent_sales' => [
            ['ref' => 'S-10078', 'branch' => 'Main', 'amount' => 'PHP 1,250'],
            ['ref' => 'S-10077', 'branch' => 'North', 'amount' => 'PHP 980'],
            ['ref' => 'S-10076', 'branch' => 'Online', 'amount' => 'PHP 2,840'],
        ],
        'low_stock' => [
            ['sku' => 'AC-CASE-001', 'item' => 'Clear Case iPhone 13', 'qty' => 4],
            ['sku' => 'LD-REG-100', 'item' => 'Load Card 100', 'qty' => 6],
        ],
        'pending_approvals' => [
            ['type' => 'Expense', 'reference' => 'EXP-2811', 'status' => 'Pending'],
            ['type' => 'Transfer', 'reference' => 'ST-0041', 'status' => 'Pending'],
        ],
        'recent_expenses' => [
            ['reference' => 'EXP-2810', 'category' => 'Utilities', 'amount' => 'PHP 3,240'],
            ['reference' => 'EXP-2809', 'category' => 'Transportation', 'amount' => 'PHP 1,090'],
        ],
        'recent_announcements' => [
            ['title' => 'Month-end inventory count schedule', 'posted_by' => 'Operations'],
            ['title' => 'New POS discount policy reminder', 'posted_by' => 'Finance'],
        ],
    ],

    'branch_metrics' => [
        ['label' => 'Branch Today Sales', 'value' => 'PHP 42,300'],
        ['label' => 'Branch Expenses', 'value' => 'PHP 5,480'],
        ['label' => 'Branch Cash Status', 'value' => 'Balanced'],
        ['label' => 'Branch Inventory Alerts', 'value' => '8 items'],
        ['label' => 'Branch Airtime Wallet', 'value' => 'PHP 12,400'],
        ['label' => 'Branch Pending Tasks', 'value' => '6 tasks'],
        ['label' => 'Branch Daily Closing', 'value' => 'Open'],
    ],
];
