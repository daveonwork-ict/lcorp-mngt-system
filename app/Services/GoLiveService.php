<?php

namespace App\Services;

use App\Models\GoLiveChecklist;

class GoLiveService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly NotificationService $notificationService,
    ) {
    }

    public function baselineItems(): array
    {
        return [
            'domain_works' => 'Domain works',
            'ssl_works' => 'SSL works',
            'login_works' => 'Login works',
            'roles_permissions_work' => 'Roles and permissions work',
            'branch_restrictions_work' => 'Branch restrictions work',
            'inventory_pos_airtime_work' => 'Inventory, POS, and airtime work',
            'cash_flow_expenses_work' => 'Cash flow and expenses work',
            'warranty_reports_work' => 'Warranty and reports work',
            'announcement_chat_work' => 'Announcements and chat work',
            'pwa_mobile_work' => 'PWA and mobile responsiveness work',
            'backups_audit_work' => 'Backups and audit logs work',
        ];
    }

    public function ensureBaseline(?int $branchId = null): void
    {
        foreach ($this->baselineItems() as $itemKey => $itemLabel) {
            GoLiveChecklist::query()->firstOrCreate(
                ['branch_id' => $branchId, 'item_key' => $itemKey],
                [
                    'checklist_number' => 'GLV-'.now()->format('YmdHis').'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT),
                    'item_label' => $itemLabel,
                    'status' => 'pending',
                ]
            );
        }
    }

    public function paginate(?int $branchId = null)
    {
        $this->ensureBaseline($branchId);

        return GoLiveChecklist::query()
            ->with(['branch', 'checker'])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderBy('item_key')
            ->paginate(30)
            ->withQueryString();
    }

    public function updateStatus(GoLiveChecklist $checklist, string $status, ?string $remarks = null): GoLiveChecklist
    {
        $before = $checklist->toArray();

        $checklist->update([
            'status' => $status,
            'remarks' => $remarks,
            'checked_by' => auth()->id(),
            'checked_at' => now(),
        ]);

        $this->auditLogService->record('deployment', 'go_live_check_updated', $before, $checklist->fresh()->toArray(), $checklist->branch_id, 'Go-live checklist item updated');

        if ($status === 'pending') {
            $this->notificationService->create(auth()->id(), $checklist->branch_id, 'Go-live checklist incomplete', $checklist->item_label.' is still pending.', 'deployment', ['go_live_checklist_id' => $checklist->id]);
        }

        return $checklist->fresh();
    }
}
