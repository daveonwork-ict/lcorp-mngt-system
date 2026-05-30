<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
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
        $validated = $this->validateAccess($module, $filePath, $fileName, $branchId, $referenceType, $referenceId);

        if ($validated instanceof RedirectResponse) {
            return $validated;
        }

        return Storage::download($filePath, $fileName);
    }

    public function preview(string $module, string $filePath, string $fileName, ?int $branchId = null, ?string $referenceType = null, ?int $referenceId = null): Response|RedirectResponse
    {
        $validated = $this->validateAccess($module, $filePath, $fileName, $branchId, $referenceType, $referenceId);

        if ($validated instanceof RedirectResponse) {
            return $validated;
        }

        return response(Storage::get($filePath), 200, [
            'Content-Type' => Storage::mimeType($filePath) ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="'.$fileName.'"',
        ]);
    }

    private function validateAccess(string $module, string $filePath, string $fileName, ?int $branchId = null, ?string $referenceType = null, ?int $referenceId = null): bool|RedirectResponse
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

        return true;
    }
}
