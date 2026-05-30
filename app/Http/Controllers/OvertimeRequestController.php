<?php

namespace App\Http\Controllers;

use App\Http\Requests\Hr\StoreOvertimeRequestRequest;
use App\Http\Requests\Hr\UpdateOvertimeRequestRequest;
use App\Models\Branch;
use App\Models\OvertimeRequest;
use App\Models\User;
use App\Services\OvertimeRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OvertimeRequestController extends Controller
{
    public function __construct(private readonly OvertimeRequestService $overtimeRequestService)
    {
    }

    public function index(Request $request): View
    {
        return view('hr.overtime.index', [
            'overtimeRequests' => $this->overtimeRequestService->paginate($request->only(['branch_id', 'status'])),
            'branches' => Branch::query()->where('is_active', true)->orderBy('branch_name')->get(),
            'filters' => $request->only(['branch_id', 'status']),
        ]);
    }

    public function create(): View
    {
        $selfService = $this->isSelfServiceUser();

        return view('hr.overtime.form', [
            'overtimeRequest' => new OvertimeRequest(),
            'branches' => Branch::query()->where('is_active', true)->orderBy('branch_name')->get(),
            'users' => $selfService ? collect([auth()->user()])->filter() : User::query()->orderBy('full_name')->get(),
            'mode' => 'create',
            'selfService' => $selfService,
        ]);
    }

    public function store(StoreOvertimeRequestRequest $request): RedirectResponse
    {
        $this->overtimeRequestService->create($request->validated());

        return redirect()->route('hr.overtime.index')->with('status', 'Overtime request created successfully.');
    }

    public function edit(OvertimeRequest $overtime): View
    {
        $this->authorizeSelfServiceRecord($overtime->user_id);
        $selfService = $this->isSelfServiceUser();

        return view('hr.overtime.form', [
            'overtimeRequest' => $overtime,
            'branches' => Branch::query()->where('is_active', true)->orderBy('branch_name')->get(),
            'users' => $selfService ? collect([auth()->user()])->filter() : User::query()->orderBy('full_name')->get(),
            'mode' => 'edit',
            'selfService' => $selfService,
        ]);
    }

    public function update(UpdateOvertimeRequestRequest $request, OvertimeRequest $overtime): RedirectResponse
    {
        $this->authorizeSelfServiceRecord($overtime->user_id);
        $this->overtimeRequestService->update($overtime, $request->validated());

        return redirect()->route('hr.overtime.index')->with('status', 'Overtime request updated successfully.');
    }

    public function approve(OvertimeRequest $overtime): RedirectResponse
    {
        $this->overtimeRequestService->review($overtime, 'approve');

        return back()->with('status', 'Overtime request approved.');
    }

    public function reject(OvertimeRequest $overtime): RedirectResponse
    {
        $this->overtimeRequestService->review($overtime, 'reject');

        return back()->with('status', 'Overtime request rejected.');
    }

    private function isSelfServiceUser(): bool
    {
        $user = auth()->user();

        return (bool) $user && ! in_array($user->role?->code, [config('rms.owner_role_code'), 'super_admin', 'branch_manager'], true);
    }

    private function authorizeSelfServiceRecord(int $userId): void
    {
        if ($this->isSelfServiceUser() && auth()->id() !== $userId) {
            abort(403, 'Overtime request access denied.');
        }
    }
}
