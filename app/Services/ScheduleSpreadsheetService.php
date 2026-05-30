<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\EmployeeSchedule;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
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
                'failed_rows' => [[
                    'row_number' => 1,
                    'employee_username' => null,
                    'schedule_date' => null,
                    'schedule_type' => null,
                    'time_in' => null,
                    'time_out' => null,
                    'break_start' => null,
                    'break_end' => null,
                    'is_rest_day' => null,
                    'branch_code' => null,
                    'error' => 'Missing required columns: '.implode(', ', $missing),
                ]],
            ];
        }

        $created = 0;
        $updated = 0;
        $errors = [];
        $failedRows = [];

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
                $message = 'employee_username is missing or not in this branch.';
                $errors[] = 'Row '.$row.': '.$message;
                $failedRows[] = $this->formatFailedRow($row, $values, $message);
                continue;
            }

            $dateText = (string) ($values['schedule_date'] ?? '');
            if ($dateText === '') {
                $message = 'schedule_date is required.';
                $errors[] = 'Row '.$row.': '.$message;
                $failedRows[] = $this->formatFailedRow($row, $values, $message);
                continue;
            }

            try {
                $scheduleDate = Carbon::parse($dateText)->toDateString();
            } catch (\Throwable) {
                $message = 'schedule_date is invalid.';
                $errors[] = 'Row '.$row.': '.$message;
                $failedRows[] = $this->formatFailedRow($row, $values, $message);
                continue;
            }

            $scheduleType = strtolower((string) ($values['schedule_type'] ?? 'fixed'));
            if (! in_array($scheduleType, ['fixed', 'rotating', 'flexible'], true)) {
                $message = 'schedule_type must be fixed, rotating, or flexible.';
                $errors[] = 'Row '.$row.': '.$message;
                $failedRows[] = $this->formatFailedRow($row, $values, $message);
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
                    $message = 'branch_code does not match selected branch.';
                    $errors[] = 'Row '.$row.': '.$message;
                    $failedRows[] = $this->formatFailedRow($row, $values, $message);
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
            'failed_rows' => $failedRows,
        ];
    }

    public function storeFailedRowsCsv(array $failedRows, int $userId): string
    {
        $token = Str::lower(Str::random(40));
        $path = 'imports/schedules/failed/'.$token.'.csv';

        $handle = fopen('php://temp', 'wb+');
        fputcsv($handle, ['row_number', 'employee_username', 'schedule_date', 'schedule_type', 'time_in', 'time_out', 'break_start', 'break_end', 'is_rest_day', 'branch_code', 'error']);

        foreach ($failedRows as $failedRow) {
            fputcsv($handle, [
                $failedRow['row_number'] ?? '',
                $failedRow['employee_username'] ?? '',
                $failedRow['schedule_date'] ?? '',
                $failedRow['schedule_type'] ?? '',
                $failedRow['time_in'] ?? '',
                $failedRow['time_out'] ?? '',
                $failedRow['break_start'] ?? '',
                $failedRow['break_end'] ?? '',
                $failedRow['is_rest_day'] ?? '',
                $failedRow['branch_code'] ?? '',
                $failedRow['error'] ?? '',
            ]);
        }

        rewind($handle);
        $content = (string) stream_get_contents($handle);
        fclose($handle);

        Storage::put($path, $content);

        Cache::put('schedule_import_failed:'.$token, [
            'path' => $path,
            'user_id' => $userId,
        ], now()->addHours(4));

        return $token;
    }

    public function failedRowsFile(string $token, int $userId): ?array
    {
        $payload = Cache::get('schedule_import_failed:'.$token);
        if (! is_array($payload)) {
            return null;
        }

        if (($payload['user_id'] ?? null) !== $userId) {
            return null;
        }

        $path = (string) ($payload['path'] ?? '');
        if ($path === '' || ! Storage::exists($path)) {
            return null;
        }

        return [
            'path' => $path,
            'name' => 'schedule-import-failed-rows-'.now()->format('YmdHis').'.csv',
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

    private function formatFailedRow(int $rowNumber, array $values, string $message): array
    {
        return [
            'row_number' => $rowNumber,
            'employee_username' => $values['employee_username'] ?? null,
            'schedule_date' => $values['schedule_date'] ?? null,
            'schedule_type' => $values['schedule_type'] ?? null,
            'time_in' => $values['time_in'] ?? null,
            'time_out' => $values['time_out'] ?? null,
            'break_start' => $values['break_start'] ?? null,
            'break_end' => $values['break_end'] ?? null,
            'is_rest_day' => $values['is_rest_day'] ?? null,
            'branch_code' => $values['branch_code'] ?? null,
            'error' => $message,
        ];
    }
}
