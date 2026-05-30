<?php

namespace App\Http\Controllers;

use App\Http\Requests\Hr\StoreLeaveRequestRequest;
use App\Http\Requests\Hr\UpdateLeaveRequestRequest;
use App\Models\Branch;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\LeaveRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveRequestController extends Controller
{
    public function __construct(private readonly LeaveRequestService $leaveRequestService)
    {
    }

    public function index(Request $request): View
    {
        return view('hr.leaves.index', [
            'leaveRequests' => $this->leaveRequestService->paginate($request->only(['branch_id', 'status'])),
            'branches' => Branch::query()->where('is_active', true)->orderBy('branch_name')->get(),
            'filters' => $request->only(['branch_id', 'status']),
        ]);
    }

    public function create(): View
    {
        $selfService = $this->isSelfServiceUser();

        return view('hr.leaves.form', [
            'leaveRequest' => new LeaveRequest(),
            'branches' => Branch::query()->where('is_active', true)->orderBy('branch_name')->get(),
            'users' => $selfService ? collect([auth()->user()])->filter() : User::query()->orderBy('full_name')->get(),
            'mode' => 'create',
            'selfService' => $selfService,
        ]);
    }

    public function store(StoreLeaveRequestRequest $request): RedirectResponse
    {
        $this->leaveRequestService->create($request->validated());

        return redirect()->route('hr.leaves.index')->with('status', 'Leave request created successfully.');
    }

    public function edit(LeaveRequest $leave): View
    {
        $this->authorizeSelfServiceRecord($leave->user_id);
        $selfService = $this->isSelfServiceUser();

        return view('hr.leaves.form', [
            'leaveRequest' => $leave,
            'branches' => Branch::query()->where('is_active', true)->orderBy('branch_name')->get(),
            'users' => $selfService ? collect([auth()->user()])->filter() : User::query()->orderBy('full_name')->get(),
            'mode' => 'edit',
            'selfService' => $selfService,
        ]);
    }

    public function update(UpdateLeaveRequestRequest $request, LeaveRequest $leave): RedirectResponse
    {
        $this->authorizeSelfServiceRecord($leave->user_id);
        $this->leaveRequestService->update($leave, $request->validated());

        return redirect()->route('hr.leaves.index')->with('status', 'Leave request updated successfully.');
    }

    private function isSelfServiceUser(): bool
    {
        $user = auth()->user();

        return (bool) $user && ! in_array($user->role?->code, [config('rms.owner_role_code'), 'super_admin', 'branch_manager'], true);
    }

    private function authorizeSelfServiceRecord(int $userId): void
    {
        if ($this->isSelfServiceUser() && auth()->id() !== $userId) {
            abort(403, 'Leave request access denied.');
        }
    }
}
