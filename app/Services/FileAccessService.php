<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileAccessService
{
    public function __construct(
        private readonly BranchAccessService $branchAccessService,
        private readonly SecurityLogService $securityLogService,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    public function download(string $module, string $filePath, string $fileName, ?int $branchId = null, ?string $referenceType = null, ?int $referenceId = null): StreamedResponse|RedirectResponse
    {
        $user = auth()->user();
        if (! $user instanceof User) {
            abort(403, 'Authentication required.');
        }

        if (! $this->branchAccessService->canAccessBranch($user, $branchId)) {
            $this->securityLogService->logFileAccess([
                'module_name' => $module,
                'branch_id' => $branchId,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'file_name' => $fileName,
                'file_path' => $filePath,
                'status' => 'denied',
            ]);
            abort(403, 'File access denied.');
        }

        if (! Storage::exists($filePath)) {
            $this->securityLogService->logFileAccess([
                'module_name' => $module,
                'branch_id' => $branchId,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'file_name' => $fileName,
                'file_path' => $filePath,
                'status' => 'missing',
            ]);

            return back()->withErrors(['file' => 'File not found.']);
        }

        $this->securityLogService->logFileAccess([
            'module_name' => $module,
            'branch_id' => $branchId,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'status' => 'success',
        ]);

        $this->auditLogService->record($module, 'file_accessed', [], [
            'file_name' => $fileName,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
        ], $branchId, 'Sensitive file downloaded');

        return Storage::download($filePath, $fileName);
    }
}
