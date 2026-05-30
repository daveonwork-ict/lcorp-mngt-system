<?php

namespace App\Http\Controllers;

use App\Http\Requests\Hr\StoreEmployeeScheduleRequest;
use App\Http\Requests\Hr\UpdateEmployeeScheduleRequest;
use App\Models\Branch;
use App\Models\EmployeeSchedule;
use App\Models\User;
use App\Services\EmployeeScheduleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeScheduleController extends Controller
{
    public function __construct(private readonly EmployeeScheduleService $employeeScheduleService)
    {
    }

    public function index(Request $request): View
    {
        return view('hr.schedules.index', [
            'schedules' => $this->employeeScheduleService->paginate($request->only(['branch_id', 'user_id', 'date_from', 'date_to'])),
            'branches' => Branch::query()->where('is_active', true)->orderBy('branch_name')->get(),
            'users' => User::query()->orderBy('full_name')->get(),
            'filters' => $request->only(['branch_id', 'user_id', 'date_from', 'date_to']),
        ]);
    }

    public function create(): View
    {
        return view('hr.schedules.form', [
            'schedule' => new EmployeeSchedule(),
            'branches' => Branch::query()->where('is_active', true)->orderBy('branch_name')->get(),
            'users' => User::query()->orderBy('full_name')->get(),
            'mode' => 'create',
        ]);
    }

    public function store(StoreEmployeeScheduleRequest $request): RedirectResponse
    {
        $this->employeeScheduleService->create($request->validated());

        return redirect()->route('hr.schedules.index')->with('status', 'Schedule saved successfully.');
    }

    public function edit(EmployeeSchedule $schedule): View
    {
        return view('hr.schedules.form', [
            'schedule' => $schedule,
            'branches' => Branch::query()->where('is_active', true)->orderBy('branch_name')->get(),
            'users' => User::query()->orderBy('full_name')->get(),
            'mode' => 'edit',
        ]);
    }

    public function update(UpdateEmployeeScheduleRequest $request, EmployeeSchedule $schedule): RedirectResponse
    {
        $this->employeeScheduleService->update($schedule, $request->validated());

        return redirect()->route('hr.schedules.index')->with('status', 'Schedule updated successfully.');
    }
}
