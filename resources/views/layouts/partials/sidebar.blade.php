@php
    $modules = config('rms.modules', []);
    $isOwnerRole = auth()->user()?->role?->code === config('rms.owner_role_code');
    $dashboardRoute = auth()->user()?->hasPermission('view_executive_dashboard')
        ? 'dashboard.owner'
        : (auth()->user()?->hasPermission('view_branch_dashboard') ? 'dashboard.branch' : null);
    $moduleIcons = [
        'pos' => 'fas fa-cash-register',
        'inventory' => 'fas fa-boxes',
        'airtime' => 'fas fa-mobile-alt',
        'cash-flow' => 'fas fa-money-bill-wave',
        'expenses' => 'fas fa-receipt',
        'warranty' => 'fas fa-shield-alt',
        'customers' => 'fas fa-user-friends',
        'suppliers' => 'fas fa-truck',
        'purchasing' => 'fas fa-shopping-cart',
        'office-supplies' => 'fas fa-box-open',
        'announcements' => 'fas fa-bullhorn',
        'chat' => 'fas fa-comments',
        'reports' => 'fas fa-chart-line',
        'approvals' => 'fas fa-check-circle',
        'audit-trail' => 'fas fa-clipboard',
        'users-roles' => 'fas fa-users',
        'branches' => 'fas fa-code-branch',
        'settings' => 'fas fa-cogs',
        'deployment' => 'fas fa-rocket',
        'hr' => 'fas fa-user-clock',
    ];
    $moduleTreeMenus = [
        'inventory' => [
            ['label' => 'Dashboard', 'route' => 'inventory.dashboard', 'permission' => 'view_inventory', 'active' => 'inventory.dashboard|inventory.index'],
            ['label' => 'Products', 'route' => 'inventory.products.index', 'permission' => 'view_inventory', 'active' => 'inventory.products.*'],
            ['label' => 'Branch Inventory', 'route' => 'inventory.branch-inventory.index', 'permission' => 'view_inventory', 'active' => 'inventory.branch-inventory.*'],
            ['label' => 'Movement Ledger', 'route' => 'inventory.movements.index', 'permission' => 'view_inventory', 'active' => 'inventory.movements.*'],
            ['label' => 'Alerts', 'route' => 'inventory.alerts.index', 'permission' => 'view_inventory', 'active' => 'inventory.alerts.*'],
            ['label' => 'Stock Ins', 'route' => 'inventory.stock-ins.index', 'permission' => 'view_stock_in', 'active' => 'inventory.stock-ins.*'],
            ['label' => 'Adjustments', 'route' => 'inventory.adjustments.index', 'permission' => 'view_stock_adjustment', 'active' => 'inventory.adjustments.*'],
            ['label' => 'Transfers', 'route' => 'inventory.transfers.index', 'permission' => 'view_inventory_transfer', 'active' => 'inventory.transfers.*'],
            ['label' => 'Physical Counts', 'route' => 'inventory.physical-counts.index', 'permission' => 'view_physical_count', 'active' => 'inventory.physical-counts.*'],
        ],
        'airtime' => [
            ['label' => 'Dashboard', 'route' => 'airtime.dashboard', 'permission' => 'view_airtime_dashboard', 'active' => 'airtime.dashboard|airtime.index'],
            ['label' => 'Providers', 'route' => 'airtime.providers.index', 'permission' => 'manage_airtime_providers', 'active' => 'airtime.providers.*'],
            ['label' => 'Wallets', 'route' => 'airtime.wallets.index', 'permission' => 'view_airtime_wallets', 'active' => 'airtime.wallets.*'],
            ['label' => 'Ledgers', 'route' => 'airtime.ledgers.index', 'permission' => 'view_airtime_wallets', 'active' => 'airtime.ledgers.*'],
            ['label' => 'Fundings', 'route' => 'airtime.fundings.index', 'permission' => 'create_wallet_funding', 'active' => 'airtime.fundings.*'],
            ['label' => 'Transactions', 'route' => 'airtime.transactions.index', 'permission' => 'view_airtime_transactions', 'active' => 'airtime.transactions.*'],
            ['label' => 'Adjustments', 'route' => 'airtime.adjustments.index', 'permission' => 'create_wallet_adjustment', 'active' => 'airtime.adjustments.*'],
            ['label' => 'Reports', 'route' => 'airtime.reports.index', 'permission' => 'view_airtime_reports', 'active' => 'airtime.reports.*'],
        ],
        'pos' => [
            ['label' => 'POS Terminal', 'route' => 'pos.index', 'permission' => 'view_pos', 'active' => 'pos.index'],
            ['label' => 'Sales', 'route' => 'sales.index', 'permission' => 'view_sales', 'active' => 'sales.index|sales.show'],
            ['label' => 'Held Transactions', 'route' => 'sales.held.index', 'permission' => 'hold_transaction', 'active' => 'sales.held.*'],
            ['label' => 'Returns', 'route' => 'sales.returns.index', 'permission' => 'create_sales_return', 'active' => 'sales.returns.*'],
            ['label' => 'Void Requests', 'route' => 'sales.voids.index', 'permission' => 'void_sale', 'active' => 'sales.voids.*'],
            ['label' => 'Payment Methods', 'route' => 'sales.payment-methods.index', 'permission' => 'manage_payment_methods', 'active' => 'sales.payment-methods.*'],
            ['label' => 'Sales Dashboard', 'route' => 'sales.dashboard', 'permission' => 'view_sales_dashboard', 'active' => 'sales.dashboard'],
        ],
        'cash-flow' => [
            ['label' => 'Dashboard', 'route' => 'cash-flow.index', 'permission' => 'view_cash_flow', 'active' => 'cash-flow.index'],
            ['label' => 'Openings', 'route' => 'finance.openings.index', 'permission' => 'create_cash_opening', 'active' => 'finance.openings.*'],
            ['label' => 'Cash In', 'route' => 'finance.cash-ins.index', 'permission' => 'create_cash_in', 'active' => 'finance.cash-ins.*'],
            ['label' => 'Cash Out', 'route' => 'finance.cash-outs.index', 'permission' => 'create_cash_out', 'active' => 'finance.cash-outs.*'],
            ['label' => 'Daily Closing', 'route' => 'finance.closings.index', 'permission' => 'view_daily_closing', 'active' => 'finance.closings.*'],
            ['label' => 'Variances', 'route' => 'finance.variances.index', 'permission' => 'view_daily_closing', 'active' => 'finance.variances.*'],
            ['label' => 'Transfers', 'route' => 'finance.transfers.index', 'permission' => 'manage_fund_transfers', 'active' => 'finance.transfers.*'],
            ['label' => 'Ledger', 'route' => 'finance.ledger.index', 'permission' => 'view_financial_ledger', 'active' => 'finance.ledger.*'],
            ['label' => 'Reports', 'route' => 'finance.reports.index', 'permission' => 'view_financial_reports', 'active' => 'finance.reports.*'],
        ],
        'warranty' => [
            ['label' => 'Dashboard', 'route' => 'warranty.index', 'permission' => 'view_warranties', 'active' => 'warranty.index'],
            ['label' => 'Records', 'route' => 'warranty.records.index', 'permission' => 'view_warranties', 'active' => 'warranty.records.*'],
            ['label' => 'Lookup', 'route' => 'warranty.lookup.index', 'permission' => 'view_warranties', 'active' => 'warranty.lookup.*'],
            ['label' => 'Claims', 'route' => 'warranty.claims.index', 'permission' => 'create_warranty_claim', 'active' => 'warranty.claims.*'],
            ['label' => 'Rules', 'route' => 'warranty.rules.index', 'permission' => 'manage_warranty_rules', 'active' => 'warranty.rules.*'],
            ['label' => 'Replacements', 'route' => 'warranty.replacements.index', 'permission' => 'manage_warranty_replacement', 'active' => 'warranty.replacements.*'],
            ['label' => 'Reports', 'route' => 'warranty.reports.index', 'permission' => 'view_warranty_reports', 'active' => 'warranty.reports.*'],
        ],
        'reports' => [
            ['label' => 'Overview', 'route' => 'reports.index', 'permission' => 'view_reports', 'active' => 'reports.index'],
            ['label' => 'Sales', 'route' => 'reports.sales.index', 'permission' => 'view_sales_reports', 'active' => 'reports.sales.*'],
            ['label' => 'Inventory', 'route' => 'reports.inventory.index', 'permission' => 'view_inventory_reports', 'active' => 'reports.inventory.*'],
            ['label' => 'Communication', 'route' => 'reports.communication.index', 'permission' => 'view_communication_reports', 'active' => 'reports.communication.*'],
            ['label' => 'Audit', 'route' => 'reports.audit.index', 'permission' => 'view_audit_reports', 'active' => 'reports.audit.*'],
        ],
        'purchasing' => [
            ['label' => 'Dashboard', 'route' => 'purchasing.index', 'permission' => 'view_purchasing_reports', 'active' => 'purchasing.index'],
            ['label' => 'Requests', 'route' => 'purchasing.requests.index', 'permission' => 'view_purchasing_reports', 'active' => 'purchasing.requests.*'],
            ['label' => 'Orders', 'route' => 'purchasing.orders.index', 'permission' => 'view_purchasing_reports', 'active' => 'purchasing.orders.*'],
            ['label' => 'Receiving', 'route' => 'purchasing.receiving-reports.index', 'permission' => 'view_purchasing_reports', 'active' => 'purchasing.receiving-reports.*'],
            ['label' => 'Payables', 'route' => 'purchasing.payables.index', 'permission' => 'view_supplier_payables', 'active' => 'purchasing.payables.*'],
            ['label' => 'Payments', 'route' => 'purchasing.payments.index', 'permission' => 'record_supplier_payment', 'active' => 'purchasing.payments.*'],
            ['label' => 'Reports', 'route' => 'purchasing.reports.index', 'permission' => 'view_purchasing_reports', 'active' => 'purchasing.reports.*'],
        ],
        'office-supplies' => [
            ['label' => 'Supplies', 'route' => 'office-supplies.index', 'permission' => 'manage_office_supplies', 'active' => 'office-supplies.index|office-supplies.store|office-supplies.update'],
            ['label' => 'Categories', 'route' => 'office-supplies.categories.index', 'permission' => 'manage_office_supplies', 'active' => 'office-supplies.categories.*'],
            ['label' => 'Inventory', 'route' => 'office-supplies.inventory.index', 'permission' => 'view_office_supply_inventory', 'active' => 'office-supplies.inventory.*'],
            ['label' => 'Issuances', 'route' => 'office-supplies.issuances.index', 'permission' => 'create_supply_issuance', 'active' => 'office-supplies.issuances.*'],
            ['label' => 'Accountabilities', 'route' => 'office-supplies.accountabilities.index', 'permission' => 'view_staff_accountability', 'active' => 'office-supplies.accountabilities.*'],
        ],
        'deployment' => [
            ['label' => 'Checklists', 'route' => 'deployment.checklists.index', 'permission' => 'view_deployment_checklists', 'active' => 'deployment.checklists.*'],
            ['label' => 'Imports', 'route' => 'deployment.imports.index', 'permission' => 'view_data_imports', 'active' => 'deployment.imports.*'],
            ['label' => 'Training', 'route' => 'deployment.training.index', 'permission' => 'view_training_logs', 'active' => 'deployment.training.*'],
            ['label' => 'Go-Live', 'route' => 'deployment.go-live.index', 'permission' => 'view_go_live_checklists', 'active' => 'deployment.go-live.*'],
            ['label' => 'Support', 'route' => 'deployment.support.index', 'permission' => 'view_support_tickets', 'active' => 'deployment.support.*'],
            ['label' => 'Acceptance', 'route' => 'deployment.acceptance.index', 'permission' => 'view_system_acceptance', 'active' => 'deployment.acceptance.*'],
        ],
        'hr' => [
            ['label' => 'Dashboard', 'route' => 'hr.dashboard', 'permission' => 'view_hr_dashboard', 'active' => 'hr.dashboard'],
            ['label' => 'Employees', 'route' => 'hr.employees.index', 'permission' => 'view_employees', 'active' => 'hr.employees.*'],
            ['label' => 'Positions', 'route' => 'hr.positions.index', 'permission' => 'view_positions', 'active' => 'hr.positions.*'],
            ['label' => 'Schedules', 'route' => 'hr.schedules.index', 'permission' => 'view_schedules', 'active' => 'hr.schedules.*'],
            ['label' => 'Attendance', 'route' => 'hr.attendance.index', 'permission' => 'view_attendance', 'active' => 'hr.attendance.*'],
            ['label' => 'Leave Requests', 'route' => 'hr.leaves.index', 'permission' => 'view_leave_requests', 'active' => 'hr.leaves.*'],
            ['label' => 'Overtime Requests', 'route' => 'hr.overtime.index', 'permission' => 'view_overtime_requests', 'active' => 'hr.overtime.*'],
            ['label' => 'Gov Contributions', 'route' => 'hr.contributions.index', 'permission' => 'view_government_contributions', 'active' => 'hr.contributions.*'],
            ['label' => 'Payroll Periods', 'route' => 'hr.payroll.periods.index', 'permission' => 'view_payroll', 'active' => 'hr.payroll.periods.*'],
            ['label' => 'Payroll Runs', 'route' => 'hr.payroll.runs.index', 'permission' => 'view_payroll', 'active' => 'hr.payroll.runs.*'],
            ['label' => 'Cash Advances', 'route' => 'hr.cash-advances.index', 'permission' => 'view_cash_advances', 'active' => 'hr.cash-advances.*'],
            ['label' => 'Employee Loans', 'route' => 'hr.loans.index', 'permission' => 'view_employee_loans', 'active' => 'hr.loans.*'],
            ['label' => 'Payslips', 'route' => 'hr.payslips.index', 'permission' => 'view_payslips', 'active' => 'hr.payslips.*'],
            ['label' => 'Reports', 'route' => 'hr.reports.index', 'permission' => 'view_hr_reports', 'active' => 'hr.reports.*'],
        ],
    ];
@endphp
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="{{ $dashboardRoute ? route($dashboardRoute) : route('login') }}" class="brand-link" style="display:flex; align-items:center; justify-content:center; padding:0.65rem 0.8rem;">
        <img src="{{ $brandingSidenavLogoUrl ?? asset('images/dits_logo.png') }}" alt="Daveonwork IT Solutions" style="max-width: 220px; max-height: 64px; width: 100%; height: auto; object-fit: contain;" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-block';">
        <i class="fas fa-store-alt" style="display:none;"></i>
    </a>

    <div class="sidebar d-flex flex-column">
        <nav class="mt-2 flex-grow-1">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                @if ($dashboardRoute)
                    <li class="nav-item">
                        <a href="{{ route($dashboardRoute) }}" class="nav-link {{ request()->routeIs('dashboard.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-chart-pie"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                @endif

                @foreach ($modules as $module)
                    @php
                        $iconClass = $moduleIcons[$module['slug']] ?? 'fas fa-circle';
                        $treeItems = $moduleTreeMenus[$module['slug']] ?? [];
                        $visibleTreeItems = collect($treeItems)->filter(function (array $item): bool {
                            return auth()->user()?->hasPermission($item['permission'] ?? '') ?? false;
                        })->values();
                        $isTreeMenu = $visibleTreeItems->isNotEmpty();
                        $canOpenModule = auth()->user()?->hasPermission($module['permission']) || $isTreeMenu;
                        $isTreeMenuOpen = $isTreeMenu && $visibleTreeItems->contains(function (array $item): bool {
                            return request()->routeIs($item['active'] ?? $item['route']);
                        });
                    @endphp
                    @continue(! $canOpenModule)
                    @if ($isTreeMenu)
                        <li class="nav-item {{ $isTreeMenuOpen ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ $isTreeMenuOpen ? 'active' : '' }}">
                                <i class="nav-icon {{ $iconClass }}"></i>
                                <p>
                                    {{ $module['name'] }}
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                @foreach ($visibleTreeItems as $item)
                                    <li class="nav-item">
                                        <a href="{{ route($item['route']) }}" class="nav-link {{ request()->routeIs($item['active'] ?? $item['route']) ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>{{ $item['label'] }}</p>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a href="{{ route($module['route']) }}" class="nav-link {{ request()->routeIs($module['route']) ? 'active' : '' }}">
                                <i class="nav-icon {{ $iconClass }}"></i>
                                <p>{{ $module['name'] }}</p>
                            </a>
                        </li>
                    @endif
                @endforeach

                <li class="nav-header">ACCESS CONTROL</li>
                @if (! $isOwnerRole && auth()->user()?->hasPermission('view_users'))
                    <li class="nav-item"><a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"><i class="nav-icon fas fa-users"></i><p>Users</p></a></li>
                @endif
                @if (! $isOwnerRole && auth()->user()?->hasPermission('view_roles'))
                    <li class="nav-item"><a href="{{ route('admin.roles.index') }}" class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}"><i class="nav-icon fas fa-user-tag"></i><p>Roles</p></a></li>
                @endif
                @if (! $isOwnerRole && auth()->user()?->hasPermission('assign_permissions'))
                    <li class="nav-item"><a href="{{ route('admin.permissions.index') }}" class="nav-link {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}"><i class="nav-icon fas fa-key"></i><p>Permissions</p></a></li>
                @endif
                @if (! $isOwnerRole && auth()->user()?->hasPermission('view_branches'))
                    <li class="nav-item"><a href="{{ route('admin.branches.index') }}" class="nav-link {{ request()->routeIs('admin.branches.*') ? 'active' : '' }}"><i class="nav-icon fas fa-code-branch"></i><p>Branch Management</p></a></li>
                @endif
                @if (auth()->user()?->hasPermission('view_audit_logs'))
                    <li class="nav-item"><a href="{{ route('admin.activity-logs.index') }}" class="nav-link {{ request()->routeIs('admin.activity-logs.*') || request()->routeIs('admin.security.*') ? 'active' : '' }}"><i class="nav-icon fas fa-shield-alt"></i><p>Security Logs</p></a></li>
                @endif
            </ul>
        </nav>

        <div style="margin-top:auto; padding:0.8rem 0.95rem calc(0.95rem + env(safe-area-inset-bottom)); border-top:1px solid rgba(255,255,255,0.2); background:linear-gradient(180deg, rgba(255,255,255,0.06), rgba(0,0,0,0.24));">
            <span style="display:block; color:rgba(255,255,255,0.98); font-size:0.86rem; font-weight:700; letter-spacing:.03em; margin-bottom:0.2rem;">Retail Management System</span>
            <div style="color:rgba(255,255,255,0.78); font-size:0.56rem; line-height:1.25;">
                Developed by
                <a href="https://www.daveonwork.com" target="_blank" rel="noopener noreferrer" style="color:#ffffff; text-decoration:underline; text-underline-offset:2px; font-weight:500;">Daveonwork IT Solutions</a>
            </div>
        </div>
    </div>
</aside>
