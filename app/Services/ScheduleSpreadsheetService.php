<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\EmployeeSchedule;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ScheduleSpreadsheetService
{
    public function __construct(private readonly EmployeeScheduleService $employeeScheduleService)
    {
    }

    public function downloadTemplate(Branch $branch, iterable $employees): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $instructionSheet = $spreadsheet->getActiveSheet();
        $instructionSheet->setTitle('Instructions');
        $instructionSheet->fromArray([
            ['Schedule Upload Template'],
            ['Branch', $branch->branch_name ?? $branch->name],
            ['Branch Code', $branch->code ?? $branch->branch_code],
            [''],
            ['How to use this template:'],
            ['1. Fill rows in the Template sheet.'],
            ['2. Use employee_username from the Branch Employees sheet.'],
            ['3. Use schedule_type: fixed, rotating, or flexible.'],
            ['4. Use is_rest_day values: 1/0, yes/no, true/false.'],
            ['5. Time format should be HH:MM (24-hour).'],
            ['6. Date format should be YYYY-MM-DD.'],
        ]);

        $templateSheet = $spreadsheet->createSheet();
        $templateSheet->setTitle('Template');
        $headers = [
            'employee_username',
            'schedule_date',
            'schedule_type',
            'time_in',
            'time_out',
            'break_start',
            'break_end',
            'is_rest_day',
            'branch_code',
        ];
        $templateSheet->fromArray([$headers], null, 'A1');
        $templateSheet->fromArray([['jdoe', now()->toDateString(), 'fixed', '08:00', '17:00', '12:00', '13:00', '0', (string) ($branch->code ?? $branch->branch_code)]], null, 'A2');

        $employeeSheet = $spreadsheet->createSheet();
        $employeeSheet->setTitle('Branch Employees');
        $employeeSheet->fromArray([['employee_id', 'display_name', 'username', 'email', 'primary_branch']], null, 'A1');

        $row = 2;
        foreach ($employees as $employee) {
            if (! $employee instanceof User) {
                continue;
            }

            $employeeSheet->fromArray([[
                $employee->id,
                $employee->display_name,
                $employee->username,
                $employee->email,
                $employee->primaryBranch?->branch_name ?? $employee->primaryBranch?->name,
            ]], null, 'A'.$row);
            $row++;
        }

        return response()->streamDownload(function () use ($spreadsheet): void {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 'schedule-template.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function importForBranch(UploadedFile $file, Branch $branch, iterable $employees): array
    {
        $employeeByUsername = [];
        foreach ($employees as $employee) {
            if (! $employee instanceof User || ! $employee->username) {
                continue;
            }

            $employeeByUsername[strtolower((string) $employee->username)] = $employee;
        }

        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getSheetByName('Template') ?? $spreadsheet->getActiveSheet();
        $highestColumn = Coordinate::columnIndexFromString($sheet->getHighestColumn());
        $highestRow = $sheet->getHighestDataRow();

        $headers = [];
        for ($col = 1; $col <= $highestColumn; $col++) {
            $cell = Coordinate::stringFromColumnIndex($col).'1';
            $headers[$col] = strtolower(trim((string) $sheet->getCell($cell)->getFormattedValue()));
        }

        $required = ['employee_username', 'schedule_date', 'schedule_type', 'is_rest_day'];
        $missing = array_values(array_diff($required, $headers));
        if ($missing !== []) {
            return [
                'created' => 0,
                'updated' => 0,
                'failed' => 1,
                'errors' => ['Missing required columns: '.implode(', ', $missing)],
            ];
        }

        $created = 0;
        $updated = 0;
        $errors = [];

        for ($row = 2; $row <= $highestRow; $row++) {
            $values = [];
            for ($col = 1; $col <= $highestColumn; $col++) {
                $header = $headers[$col] ?? null;
                if (! $header) {
                    continue;
                }

                $cell = Coordinate::stringFromColumnIndex($col).$row;
                $values[$header] = trim((string) $sheet->getCell($cell)->getFormattedValue());
            }

            if ($this->isEmptyRow($values)) {
                continue;
            }

            $username = strtolower((string) ($values['employee_username'] ?? ''));
            $employee = $employeeByUsername[$username] ?? null;
            if (! $employee) {
                $errors[] = 'Row '.$row.': employee_username is missing or not in this branch.';
                continue;
            }

            $dateText = (string) ($values['schedule_date'] ?? '');
            if ($dateText === '') {
                $errors[] = 'Row '.$row.': schedule_date is required.';
                continue;
            }

            try {
                $scheduleDate = Carbon::parse($dateText)->toDateString();
            } catch (\Throwable) {
                $errors[] = 'Row '.$row.': schedule_date is invalid.';
                continue;
            }

            $scheduleType = strtolower((string) ($values['schedule_type'] ?? 'fixed'));
            if (! in_array($scheduleType, ['fixed', 'rotating', 'flexible'], true)) {
                $errors[] = 'Row '.$row.': schedule_type must be fixed, rotating, or flexible.';
                continue;
            }

            $restDay = $this->parseBoolean($values['is_rest_day'] ?? '0');
            $timeIn = $restDay ? null : $this->normalizeTime($values['time_in'] ?? null);
            $timeOut = $restDay ? null : $this->normalizeTime($values['time_out'] ?? null);
            $breakStart = $restDay ? null : $this->normalizeTime($values['break_start'] ?? null);
            $breakEnd = $restDay ? null : $this->normalizeTime($values['break_end'] ?? null);

            if (($values['branch_code'] ?? '') !== '') {
                $rowBranch = strtolower((string) ($values['branch_code'] ?? ''));
                $expectedBranch = strtolower((string) ($branch->code ?? $branch->branch_code));
                if ($rowBranch !== $expectedBranch) {
                    $errors[] = 'Row '.$row.': branch_code does not match selected branch.';
                    continue;
                }
            }

            $exists = EmployeeSchedule::query()
                ->where('user_id', $employee->id)
                ->whereDate('schedule_date', $scheduleDate)
                ->exists();

            $this->employeeScheduleService->create([
                'user_id' => $employee->id,
                'branch_id' => $branch->id,
                'schedule_date' => $scheduleDate,
                'schedule_type' => $scheduleType,
                'time_in' => $timeIn,
                'time_out' => $timeOut,
                'break_start' => $breakStart,
                'break_end' => $breakEnd,
                'is_rest_day' => $restDay,
            ]);

            if ($exists) {
                $updated++;
            } else {
                $created++;
            }
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'failed' => count($errors),
            'errors' => array_slice($errors, 0, 10),
        ];
    }

    private function isEmptyRow(array $values): bool
    {
        foreach ($values as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function parseBoolean(mixed $value): bool
    {
        $text = strtolower(trim((string) $value));

        return in_array($text, ['1', 'true', 'yes', 'y'], true);
    }

    private function normalizeTime(mixed $value): ?string
    {
        $text = trim((string) ($value ?? ''));
        if ($text === '') {
            return null;
        }

        try {
            return Carbon::parse($text)->format('H:i');
        } catch (\Throwable) {
            return null;
        }
    }
}
