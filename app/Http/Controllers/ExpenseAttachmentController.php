<?php

namespace App\Http\Controllers;

use App\Models\ExpenseAttachment;
use App\Services\BranchAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExpenseAttachmentController extends Controller
{
    public function __construct(private readonly BranchAccessService $branchAccessService)
    {
    }

    public function download(ExpenseAttachment $attachment): StreamedResponse|RedirectResponse
    {
        $expense = $attachment->expense;
        $user = auth()->user();

        if (! $user || ! $expense || ! $this->branchAccessService->canAccessBranch($user, $expense->branch_id)) {
            abort(403, 'Branch access denied.');
        }

        if (! Storage::exists($attachment->file_path)) {
            return back()->withErrors(['file' => 'File not found.']);
        }

        return Storage::download($attachment->file_path, $attachment->file_name);
    }
}
