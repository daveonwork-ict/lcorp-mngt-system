<?php

namespace App\Services;

use App\Models\Expense;

class ExpenseApprovalService
{
    public function __construct(
        private readonly CashOutService $cashOutService,
        private readonly NotificationService $notificationService,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    public function approve(Expense $expense): Expense
    {
        if (! in_array($expense->status, ['pending', 'draft'], true)) {
            abort(422, 'Only pending expenses can be approved.');
        }

        if (! (bool) config('rms.finance.allow_self_expense_approval', false) && $expense->submitted_by === auth()->id()) {
            abort(422, 'Self-approval is not allowed.');
        }

        $before = $expense->toArray();

        $expense->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'rejected_by' => null,
            'rejected_at' => null,
            'rejection_reason' => null,
        ]);

        $this->cashOutService->record([
            'branch_id' => $expense->branch_id,
            'source_type' => 'expense',
            'source_reference_type' => 'expense',
            'source_reference_id' => $expense->id,
            'amount' => $expense->amount,
            'payment_method_id' => $expense->payment_method_id,
            'released_by' => auth()->id(),
            'released_at' => now(),
            'remarks' => 'Auto-posted for approved expense '.$expense->expense_number,
        ]);

        $this->auditLogService->record('finance', 'expense_approved', $before, $expense->toArray(), $expense->branch_id, 'Expense approved');
        $this->notificationService->create($expense->submitted_by, $expense->branch_id, 'Expense approved', 'Expense '.$expense->expense_number.' was approved.', 'finance', ['expense_id' => $expense->id]);

        return $expense;
    }

    public function reject(Expense $expense, string $reason): Expense
    {
        if (! in_array($expense->status, ['pending', 'draft'], true)) {
            abort(422, 'Only pending expenses can be rejected.');
        }

        $before = $expense->toArray();

        $expense->update([
            'status' => 'rejected',
            'rejected_by' => auth()->id(),
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);

        $this->auditLogService->record('finance', 'expense_rejected', $before, $expense->toArray(), $expense->branch_id, 'Expense rejected');
        $this->notificationService->create($expense->submitted_by, $expense->branch_id, 'Expense rejected', 'Expense '.$expense->expense_number.' was rejected.', 'finance', ['expense_id' => $expense->id]);

        return $expense;
    }
}
