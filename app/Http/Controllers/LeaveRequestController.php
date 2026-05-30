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
        return view('hr.leaves.form', [
            'leaveRequest' => new LeaveRequest(),
            'branches' => Branch::query()->where('is_active', true)->orderBy('branch_name')->get(),
            'users' => User::query()->orderBy('full_name')->get(),
            'mode' => 'create',
        ]);
    }

    public function store(StoreLeaveRequestRequest $request): RedirectResponse
    {
        $this->leaveRequestService->create($request->validated());

        return redirect()->route('hr.leaves.index')->with('status', 'Leave request created successfully.');
    }

    public function edit(LeaveRequest $leave): View
    {
        return view('hr.leaves.form', [
            'leaveRequest' => $leave,
            'branches' => Branch::query()->where('is_active', true)->orderBy('branch_name')->get(),
            'users' => User::query()->orderBy('full_name')->get(),
            'mode' => 'edit',
        ]);
    }

    public function update(UpdateLeaveRequestRequest $request, LeaveRequest $leave): RedirectResponse
    {
        $this->leaveRequestService->update($leave, $request->validated());

        return redirect()->route('hr.leaves.index')->with('status', 'Leave request updated successfully.');
    }
}
