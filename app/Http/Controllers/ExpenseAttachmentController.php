<?php

namespace App\Http\Controllers;

use App\Models\ExpenseAttachment;
use App\Services\BranchAccessService;
use App\Services\FileAccessService;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExpenseAttachmentController extends Controller
{
    public function __construct(
        private readonly BranchAccessService $branchAccessService,
        private readonly FileAccessService $fileAccessService,
    ) {
    }

    public function download(ExpenseAttachment $attachment): StreamedResponse|RedirectResponse
    {
        $expense = $attachment->expense;
        $user = auth()->user();

        if (! $user || ! $expense || ! $this->branchAccessService->canAccessBranch($user, $expense->branch_id)) {
            abort(403, 'Branch access denied.');
        }

        return $this->fileAccessService->download(
            'finance',
            $attachment->file_path,
            $attachment->file_name,
            $expense->branch_id,
            'expense_attachment',
            $attachment->id
        );
    }
}
