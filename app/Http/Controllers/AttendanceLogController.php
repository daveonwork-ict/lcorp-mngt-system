<?php

namespace App\Http\Controllers;

use App\Http\Requests\Hr\StoreAttendanceLogRequest;
use App\Http\Requests\Hr\UpdateAttendanceLogRequest;
use App\Models\AttendanceLog;
use App\Models\Branch;
use App\Models\EmployeeSchedule;
use App\Models\User;
use App\Services\AttendanceLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceLogController extends Controller
{
    public function __construct(private readonly AttendanceLogService $attendanceLogService)
    {
    }

    public function index(Request $request): View
    {
        return view('hr.attendance.index', [
            'attendanceLogs' => $this->attendanceLogService->paginate($request->only(['branch_id', 'user_id', 'date_from', 'date_to'])),
            'branches' => Branch::query()->where('is_active', true)->orderBy('branch_name')->get(),
            'users' => User::query()->orderBy('full_name')->get(),
            'filters' => $request->only(['branch_id', 'user_id', 'date_from', 'date_to']),
        ]);
    }

    public function create(): View
    {
        return view('hr.attendance.form', [
            'attendanceLog' => new AttendanceLog(),
            'branches' => Branch::query()->where('is_active', true)->orderBy('branch_name')->get(),
            'users' => User::query()->orderBy('full_name')->get(),
            'schedules' => EmployeeSchedule::query()->orderByDesc('schedule_date')->limit(200)->get(),
            'mode' => 'create',
        ]);
    }

    public function store(StoreAttendanceLogRequest $request): RedirectResponse
    {
        $this->attendanceLogService->create($request->validated());

        return redirect()->route('hr.attendance.index')->with('status', 'Attendance recorded successfully.');
    }

    public function edit(AttendanceLog $attendance): View
    {
        return view('hr.attendance.form', [
            'attendanceLog' => $attendance,
            'branches' => Branch::query()->where('is_active', true)->orderBy('branch_name')->get(),
            'users' => User::query()->orderBy('full_name')->get(),
            'schedules' => EmployeeSchedule::query()->orderByDesc('schedule_date')->limit(200)->get(),
            'mode' => 'edit',
        ]);
    }

    public function update(UpdateAttendanceLogRequest $request, AttendanceLog $attendance): RedirectResponse
    {
        $this->attendanceLogService->update($attendance, $request->validated());

        return redirect()->route('hr.attendance.index')->with('status', 'Attendance updated successfully.');
    }
}
