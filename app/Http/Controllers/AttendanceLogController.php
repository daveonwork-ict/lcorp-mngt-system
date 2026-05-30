<?php

namespace App\Http\Controllers;

use App\Http\Requests\Hr\StoreAttendanceLogRequest;
use App\Http\Requests\Hr\UpdateAttendanceLogRequest;
use App\Models\AttendanceLog;
use App\Models\Branch;
use App\Models\EmployeeSchedule;
use App\Models\User;
use App\Services\AttendanceLogService;
use App\Services\FileAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceLogController extends Controller
{
    public function __construct(
        private readonly AttendanceLogService $attendanceLogService,
        private readonly FileAccessService $fileAccessService,
    ) {
    }

    public function index(Request $request): View
    {
        $selfService = $this->isSelfServiceUser();
        $user = $request->user();
        $filters = $request->only(['branch_id', 'user_id', 'date_from', 'date_to']);

        if ($selfService && $user) {
            $filters['user_id'] = $user->id;
            $filters['branch_id'] = $user->primary_branch_id;
        }

        return view('hr.attendance.index', [
            'attendanceLogs' => $this->attendanceLogService->paginate($filters),
            'branches' => $selfService && $user
                ? Branch::query()->whereKey($user->primary_branch_id)->where('is_active', true)->orderBy('branch_name')->get()
                : Branch::query()->where('is_active', true)->orderBy('branch_name')->get(),
            'users' => $selfService && $user
                ? collect([$user])
                : User::query()->orderBy('full_name')->get(),
            'filters' => $filters,
            'selfService' => $selfService,
        ]);
    }

    public function create(): View|RedirectResponse
    {
        $selfService = $this->isSelfServiceUser();

        if ($selfService) {
            $user = auth()->user();
            $todayDate = now('Asia/Manila')->toDateString();
            $todaySchedule = EmployeeSchedule::query()
                ->where('user_id', $user?->id)
                ->whereDate('schedule_date', $todayDate)
                ->latest('id')
                ->first();

            if (! $todaySchedule || $todaySchedule->is_rest_day) {
                return redirect()
                    ->route('hr.attendance.index')
                    ->withErrors(['schedule_id' => 'No plotted schedule for today. Attendance check-in/out is not allowed.']);
            }

            $todayAttendance = AttendanceLog::query()
                ->where('user_id', $user?->id)
                ->whereDate('attendance_date', $todayDate)
                ->latest('id')
                ->first();

            if ($todayAttendance && $todayAttendance->time_in && $todayAttendance->time_out) {
                return redirect()
                    ->route('hr.attendance.index')
                    ->with('status', 'Today\'s attendance is already completed.');
            }

            $attendanceAction = ($todayAttendance && $todayAttendance->time_in && ! $todayAttendance->time_out)
                ? 'clock_out'
                : 'clock_in';

            return view('hr.attendance.form', [
                'attendanceLog' => $todayAttendance ?? new AttendanceLog([
                    'user_id' => $user?->id,
                    'branch_id' => $user?->primary_branch_id,
                    'attendance_date' => $todayDate,
                    'schedule_id' => $todaySchedule->id,
                ]),
                'branches' => Branch::query()->where('is_active', true)->orderBy('branch_name')->get(),
                'users' => collect([$user])->filter(),
                'schedules' => collect([$todaySchedule]),
                'mode' => 'create',
                'selfService' => true,
                'todaySchedule' => $todaySchedule,
                'attendanceAction' => $attendanceAction,
            ]);
        }

        return view('hr.attendance.form', [
            'attendanceLog' => new AttendanceLog(),
            'branches' => Branch::query()->where('is_active', true)->orderBy('branch_name')->get(),
            'users' => User::query()->orderBy('full_name')->get(),
            'schedules' => EmployeeSchedule::query()->orderByDesc('schedule_date')->limit(200)->get(),
            'mode' => 'create',
            'selfService' => false,
            'attendanceAction' => 'clock_in',
        ]);
    }

    public function show(AttendanceLog $attendance): View
    {
        $this->authorizeSelfServiceRecord($attendance->user_id);
        $attendance->load(['user', 'branch', 'schedule']);

        return view('hr.attendance.show', [
            'attendanceLog' => $attendance,
            'verificationIn' => $this->attendanceLogService->verifyCaptureMetadata($attendance, 'in'),
            'verificationOut' => $this->attendanceLogService->verifyCaptureMetadata($attendance, 'out'),
        ]);
    }

    public function reverify(AttendanceLog $attendance): RedirectResponse
    {
        $this->authorizeSelfServiceRecord($attendance->user_id);
        $verificationIn = $this->attendanceLogService->verifyCaptureMetadata($attendance, 'in');
        $verificationOut = $this->attendanceLogService->verifyCaptureMetadata($attendance, 'out');

        $summary = collect([$verificationIn['label'] ?? 'N/A', $verificationOut['label'] ?? 'N/A'])->implode(' / ');

        return redirect()
            ->route('hr.attendance.show', $attendance)
            ->with('status', 'Attendance capture verification refreshed: '.$summary);
    }

    public function store(StoreAttendanceLogRequest $request): RedirectResponse
    {
        $this->attendanceLogService->create($request->validated());

        return redirect()->route('hr.attendance.index')->with('status', 'Attendance recorded successfully.');
    }

    public function edit(AttendanceLog $attendance): View
    {
        $this->authorizeSelfServiceRecord($attendance->user_id);
        $selfService = $this->isSelfServiceUser();

        return view('hr.attendance.form', [
            'attendanceLog' => $attendance,
            'branches' => Branch::query()->where('is_active', true)->orderBy('branch_name')->get(),
            'users' => $selfService ? collect([auth()->user()])->filter() : User::query()->orderBy('full_name')->get(),
            'schedules' => EmployeeSchedule::query()->orderByDesc('schedule_date')->limit(200)->get(),
            'mode' => 'edit',
            'selfService' => $selfService,
        ]);
    }

    public function update(UpdateAttendanceLogRequest $request, AttendanceLog $attendance): RedirectResponse
    {
        $this->authorizeSelfServiceRecord($attendance->user_id);
        $this->attendanceLogService->update($attendance, $request->validated());

        return redirect()->route('hr.attendance.index')->with('status', 'Attendance updated successfully.');
    }

    public function previewSelfie(AttendanceLog $attendance, string $captureType): Response|RedirectResponse
    {
        $this->authorizeSelfServiceRecord($attendance->user_id);
        abort_unless(in_array($captureType, ['in', 'out'], true), 404);

        $filePath = $captureType === 'out' ? $attendance->selfie_time_out_path : $attendance->selfie_time_in_path;
        if (! $filePath) {
            return back()->withErrors(['file' => 'Selfie file not found.']);
        }

        return $this->fileAccessService->preview(
            'hr_attendance',
            $filePath,
            basename($filePath),
            $attendance->branch_id,
            'attendance_log_'.$captureType,
            $attendance->id,
        );
    }

    private function isSelfServiceUser(): bool
    {
        $user = auth()->user();

        return (bool) $user && ! in_array($user->role?->code, [config('rms.owner_role_code'), 'super_admin', 'branch_manager'], true);
    }

    private function authorizeSelfServiceRecord(int $userId): void
    {
        if ($this->isSelfServiceUser() && auth()->id() !== $userId) {
            abort(403, 'Attendance access denied.');
        }
    }
}
