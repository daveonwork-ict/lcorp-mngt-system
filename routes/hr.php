<?php

use App\Http\Controllers\EmployeeProfileController;
use App\Http\Controllers\EmployeeScheduleController;
use App\Http\Controllers\GovernmentContributionController;
use App\Http\Controllers\HrDashboardController;
use App\Http\Controllers\HrReportController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\AttendanceLogController;
use App\Http\Controllers\CashAdvanceController;
use App\Http\Controllers\PayslipController;
use App\Http\Controllers\OvertimeRequestController;
use App\Http\Controllers\PayrollPeriodController;
use App\Http\Controllers\PayrollRunController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\PositionController;
use Illuminate\Support\Facades\Route;

Route::prefix('hr')->name('hr.')->group(function (): void {
    Route::get('/dashboard', [HrDashboardController::class, 'index'])
        ->middleware('permission:view_hr_dashboard')
        ->name('dashboard');

    Route::middleware('permission:view_employees')->group(function (): void {
        Route::resource('/employees', EmployeeProfileController::class)
            ->parameters(['employees' => 'employee'])
            ->except(['show', 'destroy']);
    });

    Route::middleware('permission:view_positions')->group(function (): void {
        Route::resource('/positions', PositionController::class)
            ->except(['show', 'destroy']);
    });

    Route::middleware('permission:view_schedules')->group(function (): void {
        Route::get('/schedules', [EmployeeScheduleController::class, 'index'])->name('schedules.index');
    });

    Route::middleware('permission:manage_schedules')->group(function (): void {
        Route::get('/schedules/create', [EmployeeScheduleController::class, 'create'])->name('schedules.create');
        Route::post('/schedules', [EmployeeScheduleController::class, 'store'])->name('schedules.store');
        Route::get('/schedules/template', [EmployeeScheduleController::class, 'template'])->name('schedules.template');
        Route::post('/schedules/import', [EmployeeScheduleController::class, 'import'])->name('schedules.import');
        Route::get('/schedules/import/failed/{token}', [EmployeeScheduleController::class, 'downloadFailedImport'])->name('schedules.import.failed.download');
        Route::get('/schedules/{schedule}/edit', [EmployeeScheduleController::class, 'edit'])->name('schedules.edit');
        Route::match(['PUT', 'PATCH'], '/schedules/{schedule}', [EmployeeScheduleController::class, 'update'])->name('schedules.update');
    });

    Route::middleware('permission:view_leave_requests')->group(function (): void {
        Route::resource('/leaves', LeaveRequestController::class)
            ->parameters(['leaves' => 'leave'])
            ->except(['show', 'destroy']);
    });

    Route::get('/attendance/{attendance}/selfies/{captureType}', [AttendanceLogController::class, 'previewSelfie'])
        ->name('attendance.selfies.preview');
    Route::post('/attendance/{attendance}/reverify', [AttendanceLogController::class, 'reverify'])
        ->name('attendance.reverify');
    Route::resource('/attendance', AttendanceLogController::class)
        ->parameters(['attendance' => 'attendance'])
        ->except(['destroy']);

    Route::middleware('permission:view_overtime_requests')->group(function (): void {
        Route::resource('/overtime', OvertimeRequestController::class)
            ->parameters(['overtime' => 'overtime'])
            ->except(['show', 'destroy']);
    });

    Route::middleware('permission:review_overtime_request')->group(function (): void {
        Route::post('/overtime/{overtime}/approve', [OvertimeRequestController::class, 'approve'])->name('overtime.approve');
        Route::post('/overtime/{overtime}/reject', [OvertimeRequestController::class, 'reject'])->name('overtime.reject');
    });

    Route::middleware('permission:view_payroll')->group(function (): void {
        Route::get('/payroll/periods', [PayrollPeriodController::class, 'index'])->name('payroll.periods.index');
        Route::post('/payroll/periods', [PayrollPeriodController::class, 'store'])->name('payroll.periods.store');

        Route::get('/payroll/runs', [PayrollRunController::class, 'index'])->name('payroll.runs.index');
        Route::post('/payroll/runs/generate', [PayrollRunController::class, 'generate'])
            ->middleware('permission:process_payroll')
            ->name('payroll.runs.generate');
        Route::get('/payroll/runs/{run}', [PayrollRunController::class, 'show'])->name('payroll.runs.show');
        Route::post('/payroll/runs/{run}/submit', [PayrollRunController::class, 'submit'])
            ->middleware('permission:process_payroll')
            ->name('payroll.runs.submit');
        Route::post('/payroll/runs/{run}/approve', [PayrollRunController::class, 'approve'])
            ->middleware('permission:approve_payroll')
            ->name('payroll.runs.approve');
        Route::post('/payroll/runs/{run}/release', [PayrollRunController::class, 'release'])
            ->middleware('permission:release_payroll')
            ->name('payroll.runs.release');
    });

    Route::middleware('permission:view_government_contributions')->group(function (): void {
        Route::get('/contributions', [GovernmentContributionController::class, 'index'])->name('contributions.index');
        Route::post('/contributions/sss', [GovernmentContributionController::class, 'storeSss'])
            ->middleware('permission:manage_government_contributions')
            ->name('contributions.sss.store');
        Route::post('/contributions/philhealth', [GovernmentContributionController::class, 'storePhilhealth'])
            ->middleware('permission:manage_government_contributions')
            ->name('contributions.philhealth.store');
        Route::post('/contributions/pagibig', [GovernmentContributionController::class, 'storePagibig'])
            ->middleware('permission:manage_government_contributions')
            ->name('contributions.pagibig.store');
        Route::post('/contributions/tax', [GovernmentContributionController::class, 'storeTax'])
            ->middleware('permission:manage_government_contributions')
            ->name('contributions.tax.store');
    });

    Route::middleware('permission:view_cash_advances')->group(function (): void {
        Route::get('/cash-advances', [CashAdvanceController::class, 'index'])->name('cash-advances.index');
        Route::post('/cash-advances', [CashAdvanceController::class, 'store'])
            ->middleware('permission:manage_cash_advances')
            ->name('cash-advances.store');
        Route::post('/cash-advances/{cashAdvance}/approve', [CashAdvanceController::class, 'approve'])
            ->middleware('permission:manage_cash_advances')
            ->name('cash-advances.approve');
        Route::post('/cash-advances/{cashAdvance}/release', [CashAdvanceController::class, 'release'])
            ->middleware('permission:manage_cash_advances')
            ->name('cash-advances.release');
    });

    Route::middleware('permission:view_employee_loans')->group(function (): void {
        Route::get('/loans', [LoanController::class, 'index'])->name('loans.index');
        Route::post('/loans', [LoanController::class, 'store'])
            ->middleware('permission:manage_employee_loans')
            ->name('loans.store');
        Route::post('/loans/{loan}/approve', [LoanController::class, 'approve'])
            ->middleware('permission:manage_employee_loans')
            ->name('loans.approve');
        Route::post('/loans/{loan}/release', [LoanController::class, 'release'])
            ->middleware('permission:manage_employee_loans')
            ->name('loans.release');
    });

    Route::middleware('permission:view_payslips')->group(function (): void {
        Route::get('/payslips', [PayslipController::class, 'index'])->name('payslips.index');
        Route::post('/payslips/{item}/generate', [PayslipController::class, 'generate'])
            ->middleware('permission:generate_payslips')
            ->name('payslips.generate');
        Route::get('/payslips/{payslip}/download', [PayslipController::class, 'download'])->name('payslips.download');
        Route::get('/payslips/{payslip}/print', [PayslipController::class, 'print'])->name('payslips.print');
    });

    Route::middleware('permission:view_hr_reports')->group(function (): void {
        Route::get('/reports', [HrReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/print', [HrReportController::class, 'printView'])->name('reports.print');
        Route::get('/reports/export-csv', [HrReportController::class, 'exportCsv'])
            ->middleware('permission:export_hr_reports')
            ->name('reports.export-csv');
        Route::get('/reports/export-excel', [HrReportController::class, 'exportExcel'])
            ->middleware('permission:export_hr_reports')
            ->name('reports.export-excel');
    });
});
