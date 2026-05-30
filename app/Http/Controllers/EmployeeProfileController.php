<?php

namespace App\Http\Controllers;

use App\Http\Requests\Hr\StoreEmployeeProfileRequest;
use App\Http\Requests\Hr\UpdateEmployeeProfileRequest;
use App\Models\Branch;
use App\Models\EmployeeProfile;
use App\Models\Position;
use App\Models\User;
use App\Services\EmployeeProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeProfileController extends Controller
{
    public function __construct(private readonly EmployeeProfileService $employeeProfileService)
    {
    }

    public function index(Request $request): View
    {
        return view('hr.employees.index', [
            'profiles' => $this->employeeProfileService->paginate($request->only(['search', 'branch_id', 'employment_status'])),
            'branches' => Branch::query()->where('is_active', true)->orderBy('branch_name')->get(),
            'filters' => $request->only(['search', 'branch_id', 'employment_status']),
        ]);
    }

    public function create(): View
    {
        return view('hr.employees.form', [
            'employeeProfile' => new EmployeeProfile(),
            'users' => User::query()->orderBy('full_name')->get(),
            'branches' => Branch::query()->where('is_active', true)->orderBy('branch_name')->get(),
            'positions' => Position::query()->where('status', 'active')->orderBy('position_name')->get(),
            'mode' => 'create',
        ]);
    }

    public function store(StoreEmployeeProfileRequest $request): RedirectResponse
    {
        $profile = $this->employeeProfileService->create($request->validated());

        return redirect()->route('hr.employees.edit', $profile)->with('status', 'Employee profile created successfully.');
    }

    public function edit(EmployeeProfile $employee): View
    {
        return view('hr.employees.form', [
            'employeeProfile' => $employee,
            'users' => User::query()->orderBy('full_name')->get(),
            'branches' => Branch::query()->where('is_active', true)->orderBy('branch_name')->get(),
            'positions' => Position::query()->where('status', 'active')->orderBy('position_name')->get(),
            'mode' => 'edit',
        ]);
    }

    public function update(UpdateEmployeeProfileRequest $request, EmployeeProfile $employee): RedirectResponse
    {
        $this->employeeProfileService->update($employee, $request->validated());

        return back()->with('status', 'Employee profile updated successfully.');
    }
}
