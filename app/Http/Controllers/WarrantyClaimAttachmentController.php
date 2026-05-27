<?php

namespace App\Http\Controllers;

use App\Models\WarrantyClaim;
use App\Models\WarrantyClaimAttachment;
use App\Services\BranchAccessService;
use App\Services\WarrantyClaimService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WarrantyClaimAttachmentController extends Controller
{
    public function __construct(
        private readonly WarrantyClaimService $claimService,
        private readonly BranchAccessService $branchAccessService,
    ) {
    }

    public function store(Request $request, WarrantyClaim $claim): RedirectResponse
    {
        $validated = $request->validate([
            'attachment' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ]);

        $file = $request->file('attachment');
        $path = $file->store('warranty/claims');

        $this->claimService->addAttachment($claim, [
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $file->getClientMimeType() ?: $file->getMimeType(),
            'file_size' => $file->getSize(),
        ]);

        return back()->with('status', 'Attachment uploaded.');
    }

    public function download(WarrantyClaimAttachment $attachment): StreamedResponse|RedirectResponse
    {
        $claim = $attachment->claim;
        $user = auth()->user();

        if (! $user || ! $claim || ! $this->branchAccessService->canAccessBranch($user, $claim->branch_id)) {
            abort(403, 'Branch access denied.');
        }

        if (! Storage::exists($attachment->file_path)) {
            return back()->withErrors(['file' => 'File not found.']);
        }

        return Storage::download($attachment->file_path, $attachment->file_name);
    }
}
