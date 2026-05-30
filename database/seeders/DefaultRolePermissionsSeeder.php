<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class DefaultRolePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $allPermissionIds = Permission::query()->pluck('id')->all();

        $this->syncRolePermissions('owner', $allPermissionIds);
        $this->syncRolePermissions('super_admin', $allPermissionIds);

        $this->syncRolePermissions('branch_manager', $this->permissionIds([
            'view_branch_dashboard',
            'view_inventory', 'view_stock_in', 'create_stock_in', 'approve_stock_in',
            'view_stock_adjustment', 'create_stock_adjustment', 'approve_stock_adjustment',
            'view_inventory_transfer', 'create_inventory_transfer', 'approve_inventory_transfer', 'receive_inventory_transfer',
            'view_physical_count', 'create_physical_count',
            'view_pos', 'create_sale', 'view_sales', 'hold_transaction', 'void_sale', 'approve_void_sale', 'create_sales_return', 'approve_sales_return', 'reprint_receipt',
            'view_airtime_dashboard', 'view_airtime_wallets', 'view_airtime_transactions', 'create_airtime_transaction', 'create_wallet_funding', 'approve_wallet_funding', 'create_wallet_adjustment',
            'view_cash_flow', 'view_expenses', 'create_expense', 'approve_expense', 'view_daily_closing', 'submit_daily_closing',
            'view_customers', 'create_customer', 'edit_customer', 'view_customer_history',
            'view_warranties', 'create_warranty_claim', 'approve_warranty_claim', 'update_warranty_claim_status',
            'view_announcements', 'create_announcement', 'publish_announcement', 'access_chat', 'send_chat_message', 'view_notification_center',
            'view_reports', 'view_sales_reports', 'view_inventory_reports', 'view_financial_reports', 'view_warranty_reports',
            'view_approval_inbox', 'approve_requests', 'reject_requests', 'return_requests',
            'view_hr_dashboard', 'view_employees', 'view_schedules', 'manage_schedules',
            'view_attendance', 'record_attendance', 'view_leave_requests', 'review_leave_request',
            'view_overtime_requests', 'review_overtime_request', 'view_hr_reports',
            'view_cash_advances', 'manage_cash_advances',
            'view_employee_loans', 'manage_employee_loans',
            'view_payslips', 'export_hr_reports',
        ]));

        $this->syncRolePermissions('cashier', $this->permissionIds([
            'view_branch_dashboard',
            'view_pos', 'create_sale', 'view_sales', 'hold_transaction', 'create_sales_return', 'reprint_receipt',
            'view_inventory', 'view_stock_in',
            'view_airtime_dashboard', 'view_airtime_wallets', 'view_airtime_transactions', 'create_airtime_transaction',
            'view_customers', 'create_customer', 'edit_customer',
            'access_chat', 'send_chat_message', 'view_notification_center', 'view_announcements',
        ]));

        $this->syncRolePermissions('inventory_staff', $this->permissionIds([
            'view_branch_dashboard',
            'view_inventory', 'create_product', 'edit_product',
            'view_stock_in', 'create_stock_in',
            'view_stock_adjustment', 'create_stock_adjustment',
            'view_inventory_transfer', 'create_inventory_transfer', 'receive_inventory_transfer',
            'view_physical_count', 'create_physical_count',
            'view_inventory_reports',
            'view_suppliers', 'manage_suppliers',
            'create_purchase_request', 'create_purchase_order', 'receive_purchase_order',
            'manage_office_supplies', 'view_office_supply_inventory', 'create_supply_issuance',
            'view_staff_accountability',
        ]));

        $this->syncRolePermissions('accounting_staff', $this->permissionIds([
            'view_branch_dashboard',
            'view_cash_flow', 'create_cash_opening', 'create_cash_in', 'create_cash_out',
            'view_expenses', 'create_expense', 'approve_expense', 'reject_expense',
            'view_daily_closing', 'submit_daily_closing', 'review_daily_closing',
            'manage_fund_transfers', 'view_financial_ledger', 'view_financial_reports', 'export_financial_reports',
            'view_airtime_reports', 'export_airtime_reports',
            'view_reports', 'export_reports', 'view_sales_reports', 'view_inventory_reports', 'view_financial_reports',
            'view_supplier_payables', 'record_supplier_payment',
            'view_approval_inbox', 'approve_requests', 'reject_requests',
            'view_hr_dashboard', 'view_payroll', 'process_payroll', 'approve_payroll', 'release_payroll',
            'view_government_contributions', 'manage_government_contributions',
            'view_cash_advances', 'manage_cash_advances',
            'view_employee_loans', 'manage_employee_loans',
            'view_payslips', 'generate_payslips', 'view_hr_reports', 'export_hr_reports',
        ]));

        $this->syncRolePermissions('auditor', $this->permissionIds([
            'view_branch_dashboard',
            'view_reports', 'view_sales_reports', 'view_inventory_reports', 'view_airtime_reports', 'view_financial_reports', 'view_warranty_reports', 'view_communication_reports', 'view_audit_reports',
            'view_audit_logs', 'view_audit_trail', 'view_security_dashboard', 'view_login_activity', 'view_file_access_logs', 'view_backup_logs', 'view_security_alerts',
            'view_sales', 'view_inventory', 'view_expenses', 'view_warranties', 'view_customers', 'view_suppliers',
        ]));

        $this->syncRolePermissions('staff_user', $this->permissionIds([
            'view_branch_dashboard',
            'view_announcements', 'access_chat', 'send_chat_message', 'view_notification_center',
            'view_pos', 'view_sales', 'view_customers',
            'view_attendance', 'record_attendance',
            'view_leave_requests', 'create_leave_request',
            'view_overtime_requests', 'create_overtime_request',
            'view_payslips',
        ]));
    }

    /**
     * @param  list<string>  $codes
     * @return list<int>
     */
    private function permissionIds(array $codes): array
    {
        return Permission::query()
            ->whereIn('code', $codes)
            ->pluck('id')
            ->all();
    }

    /**
     * @param  list<int>  $permissionIds
     */
    private function syncRolePermissions(string $roleCode, array $permissionIds): void
    {
        $role = Role::query()->where('code', $roleCode)->first();

        if (! $role) {
            return;
        }

        // Enforce a standard default profile per role.
        $role->permissions()->sync($permissionIds);
    }
}
