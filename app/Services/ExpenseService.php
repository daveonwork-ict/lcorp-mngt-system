<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\ExpenseAttachment;

class ExpenseService
{
    public function __construct(
        private readonly BranchAccessService $branchAccessService,
        private readonly NotificationService $notificationService,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    public function paginate(array $filters = [])
    {
        $user = auth()->user();

        return Expense::query()
            ->with(['branch', 'category', 'paymentMethod', 'submitter', 'approver', 'attachments'])
            ->when(! $user || $user->role?->code !== config('rms.owner_role_code'), function ($query) use ($user): void {
                $ids = $user ? $this->branchAccessService->accessibleBranches($user)->pluck('id')->all() : [];
                $query->whereIn('branch_id', $ids ?: [-1]);
            })
            ->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['category_id'] ?? null, fn ($q, $categoryId) => $q->where('category_id', $categoryId))
            ->when($filters['date_from'] ?? null, fn ($q, $date) => $q->whereDate('expense_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($q, $date) => $q->whereDate('expense_date', '<=', $date))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
    }

    public function create(array $payload): Expense
    {
        $expense = Expense::query()->create([
            'expense_number' => $payload['expense_number'] ?? ('EXP-'.now()->format('YmdHis').'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT)),
            'branch_id' => $payload['branch_id'],
            'category_id' => $payload['category_id'],
            'expense_date' => $payload['expense_date'],
            'vendor_or_payee' => $payload['vendor_or_payee'],
            'amount' => $payload['amount'],
            'payment_method_id' => $payload['payment_method_id'] ?? null,
            'description' => $payload['description'] ?? null,
            'status' => $payload['status'] ?? 'pending',
            'submitted_by' => auth()->id(),
            'remarks' => $payload['remarks'] ?? null,
        ]);

        $this->auditLogService->record('finance', 'expense_submitted', [], $expense->toArray(), $expense->branch_id, 'Expense submitted');

        $this->notificationService->create(
            null,
            $expense->branch_id,
            'Expense submitted',
            'Expense '.$expense->expense_number.' was submitted for review.',
            'finance',
            ['expense_id' => $expense->id]
        );

        return $expense;
    }

    public function addAttachment(Expense $expense, array $payload): ExpenseAttachment
    {
        $attachment = $expense->attachments()->create([
            'file_name' => $payload['file_name'],
            'file_path' => $payload['file_path'],
            'file_type' => $payload['file_type'],
            'file_size' => $payload['file_size'],
            'uploaded_by' => auth()->id(),
        ]);

        $this->auditLogService->record('finance', 'expense_receipt_uploaded', [], $attachment->toArray(), $expense->branch_id, 'Expense receipt uploaded');

        return $attachment;
    }
}
