<?php

namespace App\Http\Controllers;

use App\Http\Requests\Hr\StoreEmployeeScheduleRequest;
use App\Http\Requests\Hr\UpdateEmployeeScheduleRequest;
use App\Models\Branch;
use App\Models\EmployeeSchedule;
use App\Models\User;
use App\Services\BranchAccessService;
use App\Services\EmployeeScheduleService;
use App\Services\ScheduleSpreadsheetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class EmployeeScheduleController extends Controller
{
    public function __construct(
        private readonly EmployeeScheduleService $employeeScheduleService,
        private readonly BranchAccessService $branchAccessService,
        private readonly ScheduleSpreadsheetService $scheduleSpreadsheetService,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $branches = $this->accessibleBranches($user);
        $allowedBranchIds = $branches->pluck('id')->all();

        $filters = $request->only(['branch_id', 'user_id', 'date_from', 'date_to']);
        if (($filters['branch_id'] ?? null) && ! in_array((int) $filters['branch_id'], $allowedBranchIds, true)) {
            $filters['branch_id'] = null;
        }

        return view('hr.schedules.index', [
            'schedules' => $this->employeeScheduleService->paginate(array_merge($filters, ['allowed_branch_ids' => $allowedBranchIds])),
            'branches' => $branches,
            'users' => $this->employeesForBranches($allowedBranchIds),
            'filters' => $filters,
            'canManageSchedules' => $user?->hasPermission('manage_schedules') ?? false,
        ]);
    }

    public function create(Request $request): View
    {
        $allowedBranchIds = $this->accessibleBranches($request->user())->pluck('id')->all();

        return view('hr.schedules.form', [
            'schedule' => new EmployeeSchedule(),
            'branches' => $this->accessibleBranches($request->user()),
            'users' => $this->employeesForBranches($allowedBranchIds),
            'mode' => 'create',
        ]);
    }

    public function store(StoreEmployeeScheduleRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->ensureBranchAccess($request, (int) $validated['branch_id']);
        $this->ensureUserBelongsToBranch((int) $validated['user_id'], (int) $validated['branch_id']);

        $baseData = Arr::only($validated, [
            'user_id',
            'branch_id',
            'schedule_date',
            'schedule_type',
            'time_in',
            'time_out',
            'break_start',
            'break_end',
            'is_rest_day',
        ]);

        if ((bool) ($validated['bulk_mode'] ?? false)) {
            $result = $this->employeeScheduleService->createForDateRange(array_merge($baseData, [
                'date_from' => $validated['date_from'] ?? null,
                'date_to' => $validated['date_to'] ?? null,
                'weekdays' => $validated['weekdays'] ?? [],
            ]));

            if (($result['total'] ?? 0) === 0) {
                return redirect()->route('hr.schedules.index')->with('status', 'No schedules were created because no dates matched the selected weekdays.');
            }

            return redirect()->route('hr.schedules.index')->with('status', 'Bulk schedule saved for '.$result['total'].' day(s).');
        }

        $this->employeeScheduleService->create($baseData);

        return redirect()->route('hr.schedules.index')->with('status', 'Schedule saved successfully.');
    }

    public function template(Request $request): StreamedResponse
    {
        $user = $request->user();
        $branchId = (int) ($request->input('branch_id') ?: $user?->primary_branch_id);
        $this->ensureBranchAccess($request, $branchId);

        $branch = Branch::query()->findOrFail($branchId);
        $employees = $this->employeesForBranches([$branchId]);

        return $this->scheduleSpreadsheetService->downloadTemplate($branch, $employees);
    }

    public function import(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt'],
        ]);

        $branchId = (int) $validated['branch_id'];
        $this->ensureBranchAccess($request, $branchId);

        $branch = Branch::query()->findOrFail($branchId);
        $employees = $this->employeesForBranches([$branchId]);
        $result = $this->scheduleSpreadsheetService->importForBranch($request->file('file'), $branch, $employees);

        $message = 'Import complete. Created '.$result['created'].' and updated '.$result['updated'].' schedule(s).';
        $downloadUrl = null;

        if (($result['failed'] ?? 0) > 0) {
            $message .= ' Failed rows: '.$result['failed'].'.';

            $token = $this->scheduleSpreadsheetService->storeFailedRowsCsv($result['failed_rows'] ?? [], (int) $request->user()->id);
            $downloadUrl = route('hr.schedules.import.failed.download', ['token' => $token]);
        }

        return redirect()
            ->route('hr.schedules.index', ['branch_id' => $branchId])
            ->with('status', $message)
            ->with('schedule_import_failed_download', $downloadUrl);
    }

    public function downloadFailedImport(Request $request, string $token): StreamedResponse
    {
        $file = $this->scheduleSpreadsheetService->failedRowsFile($token, (int) $request->user()->id);

        if (! $file) {
            abort(404, 'Failed import file not found.');
        }

        return response()->streamDownload(function () use ($file): void {
            echo (string) Storage::get($file['path']);
        }, $file['name'], ['Content-Type' => 'text/csv']);
    }

    public function edit(Request $request, EmployeeSchedule $schedule): View
    {
        $this->ensureBranchAccess($request, (int) $schedule->branch_id);
        $allowedBranchIds = $this->accessibleBranches($request->user())->pluck('id')->all();

        return view('hr.schedules.form', [
            'schedule' => $schedule,
            'branches' => $this->accessibleBranches($request->user()),
            'users' => $this->employeesForBranches($allowedBranchIds),
            'mode' => 'edit',
        ]);
    }

    public function update(UpdateEmployeeScheduleRequest $request, EmployeeSchedule $schedule): RedirectResponse
    {
        $validated = $request->validated();
        $this->ensureBranchAccess($request, (int) $validated['branch_id']);
        $this->ensureUserBelongsToBranch((int) $validated['user_id'], (int) $validated['branch_id']);

        $this->employeeScheduleService->update($schedule, $validated);

        return redirect()->route('hr.schedules.index')->with('status', 'Schedule updated successfully.');
    }

    private function accessibleBranches(?User $user): Collection
    {
        return $this->branchAccessService
            ->accessibleBranches($user)
            ->sortBy(fn (Branch $branch) => strtolower((string) ($branch->branch_name ?? $branch->name ?? '')))
            ->values();
    }

    private function employeesForBranches(array $branchIds): Collection
    {
        return User::query()
            ->with('primaryBranch')
            ->whereHas('branches', fn ($query) => $query->whereIn('branches.id', $branchIds))
            ->orderBy('full_name')
            ->orderBy('id')
            ->get();
    }

    private function ensureBranchAccess(Request $request, int $branchId): void
    {
        if (! $this->branchAccessService->canAccessBranch($request->user(), $branchId)) {
            abort(403, 'Branch access denied.');
        }
    }

    private function ensureUserBelongsToBranch(int $userId, int $branchId): void
    {
        $belongs = User::query()
            ->whereKey($userId)
            ->whereHas('branches', fn ($query) => $query->where('branches.id', $branchId))
            ->exists();

        if (! $belongs) {
            abort(422, 'Selected employee is not assigned to the selected branch.');
        }
    }
}
