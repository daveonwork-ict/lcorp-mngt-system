<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Customer;
use App\Models\DataImportError;
use App\Models\DataImportLog;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Supplier;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DataImportService
{
    public function __construct(
        private readonly ImportValidationService $validationService,
        private readonly AuditLogService $auditLogService,
        private readonly NotificationService $notificationService,
    ) {
    }

    public function supportedModules(): array
    {
        return $this->validationService->supportedModules();
    }

    public function paginate(array $filters = [])
    {
        return DataImportLog::query()
            ->with('importer')
            ->when($filters['module_name'] ?? null, fn ($q, $module) => $q->where('module_name', $module))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
    }

    public function preview(string $module, UploadedFile $file, int $userId): DataImportLog
    {
        $storedPath = $file->store('imports/source');
        [$headers, $rows] = $this->readCsv($storedPath);

        $expected = $this->validationService->headersFor($module);
        $headerDiff = array_diff($expected, $headers);

        if ($expected === [] || $headerDiff !== []) {
            $errors = [[
                'row_number' => 1,
                'row_payload' => ['headers' => $headers],
                'error_messages' => ['Invalid headers. Missing: '.implode(', ', $headerDiff)],
            ]];

            return $this->createPreviewLog($module, $file->getClientOriginalName(), $storedPath, $rows, [], $errors, $userId, 'invalid_template');
        }

        $result = $this->validationService->validateRows($module, $rows);

        return $this->createPreviewLog(
            $module,
            $file->getClientOriginalName(),
            $storedPath,
            $rows,
            $result['valid_rows'],
            $result['errors'],
            $userId,
            'previewed'
        );
    }

    public function confirm(DataImportLog $log, int $userId): DataImportLog
    {
        if (! $log->file_path || ! Storage::exists($log->file_path)) {
            abort(422, 'Import source file is missing.');
        }

        [$headers, $rows] = $this->readCsv($log->file_path);
        $expected = $this->validationService->headersFor($log->module_name);
        $headerDiff = array_diff($expected, $headers);

        if ($headerDiff !== []) {
            abort(422, 'Import file headers no longer match template.');
        }

        $validated = $this->validationService->validateRows($log->module_name, $rows);
        $errors = $validated['errors'];
        $successfulRows = 0;

        foreach ($validated['valid_rows'] as $row) {
            try {
                $this->importRow($log->module_name, $row['data'], $userId);
                $successfulRows++;
            } catch (\Throwable $exception) {
                $errors[] = [
                    'row_number' => $row['row_number'],
                    'row_payload' => $row['data'],
                    'error_messages' => [$exception->getMessage()],
                ];
            }
        }

        DataImportError::query()->where('data_import_log_id', $log->id)->delete();
        foreach ($errors as $error) {
            DataImportError::query()->create($error + ['data_import_log_id' => $log->id]);
        }

        $rejectedPath = null;
        if ($errors !== []) {
            $rejectedPath = $this->storeRejectedRows($log, $errors);
        }

        $status = $errors === [] ? 'completed' : ($successfulRows > 0 ? 'completed_with_errors' : 'failed');

        $log->update([
            'total_rows' => count($rows),
            'successful_rows' => $successfulRows,
            'failed_rows' => count($errors),
            'status' => $status,
            'rejected_rows_path' => $rejectedPath,
            'summary_payload' => [
                'headers' => $headers,
                'preview_rows' => array_slice(array_map(fn ($row) => $row['data'], $rows), 0, 10),
            ],
            'imported_at' => now(),
            'completed_at' => now(),
        ]);

        $this->auditLogService->record('deployment', 'data_import_completed', [], $log->fresh()->toArray(), auth()->user()?->primary_branch_id, 'Data import completed', $userId);

        if ($errors === []) {
            $this->notificationService->create($userId, null, 'Import completed', strtoupper($log->module_name).' import completed successfully.', 'deployment', ['import_log_id' => $log->id]);
        } else {
            $this->notificationService->create($userId, null, 'Import completed with errors', strtoupper($log->module_name).' import has rejected rows.', 'deployment', ['import_log_id' => $log->id]);
        }

        return $log->fresh();
    }

    public function templateContent(string $module): string
    {
        $headers = $this->validationService->headersFor($module);
        if ($headers === []) {
            abort(404, 'Unsupported module template.');
        }

        $examples = [
            'customers' => ['CUST-0001', 'Juan', 'Dela Cruz', '09171234567', 'juan@example.com', 'Main Branch', 'walk_in', 'active'],
            'suppliers' => ['SUP-0001', 'ABC Supply', 'Anna Reyes', '09181231234', 'abc@supply.local', 'Manila', '30 days', 'active'],
            'products' => ['PROD-0001', 'SKU-0001', '1234567890123', 'Sample Product', 'PHONE', 'SAMSUNG', '1000', '1200', '5', 'active'],
        ];

        $lines = [implode(',', $headers)];
        $lines[] = implode(',', $examples[$module] ?? array_fill(0, count($headers), ''));

        return implode("\n", $lines)."\n";
    }

    private function createPreviewLog(
        string $module,
        string $originalFileName,
        string $storedPath,
        array $rows,
        array $validRows,
        array $errors,
        int $userId,
        string $status,
    ): DataImportLog {
        $log = DataImportLog::query()->create([
            'import_number' => 'IMP-'.now()->format('YmdHis').'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT),
            'module_name' => $module,
            'file_name' => $originalFileName,
            'file_path' => $storedPath,
            'total_rows' => count($rows),
            'successful_rows' => count($validRows),
            'failed_rows' => count($errors),
            'status' => $status,
            'summary_payload' => [
                'headers' => array_keys($rows[0]['data'] ?? []),
                'preview_rows' => array_slice(array_map(fn ($row) => $row['data'], $rows), 0, 10),
            ],
            'imported_by' => $userId,
        ]);

        foreach ($errors as $error) {
            DataImportError::query()->create($error + ['data_import_log_id' => $log->id]);
        }

        $this->auditLogService->record('deployment', 'data_import_previewed', [], $log->toArray(), auth()->user()?->primary_branch_id, 'Data import preview generated', $userId);

        return $log;
    }

    private function readCsv(string $path): array
    {
        $stream = Storage::readStream($path);
        if (! $stream) {
            return [[], []];
        }

        $rows = [];
        $headers = null;
        $line = 0;

        while (($data = fgetcsv($stream)) !== false) {
            $line++;
            $data = array_map(static fn ($value) => trim((string) $value), $data);

            if ($line === 1) {
                $headers = array_map(static fn ($header) => strtolower((string) $header), $data);
                continue;
            }

            if ($headers === null) {
                continue;
            }

            $assoc = [];
            foreach ($headers as $index => $header) {
                $assoc[$header] = $data[$index] ?? '';
            }

            if (collect($assoc)->filter(fn ($value) => $value !== '')->isEmpty()) {
                continue;
            }

            $rows[] = [
                'row_number' => $line,
                'data' => $assoc,
            ];
        }

        fclose($stream);

        return [$headers ?: [], $rows];
    }

    private function importRow(string $module, array $row, int $userId): void
    {
        if ($module === 'customers') {
            $first = $row['first_name'];
            $last = $row['last_name'];
            $middle = $row['middle_name'] ?? null;
            $suffix = $row['suffix'] ?? null;

            $fullName = trim(implode(' ', array_filter([$first, $middle, $last, $suffix])));

            Customer::query()->updateOrCreate(
                ['customer_code' => $row['customer_code']],
                [
                    'first_name' => $first,
                    'middle_name' => $middle,
                    'last_name' => $last,
                    'suffix' => $suffix,
                    'full_name' => $fullName,
                    'mobile_number' => $row['mobile_number'],
                    'email' => $row['email'] ?: null,
                    'address' => $row['address'] ?: null,
                    'customer_type' => $row['customer_type'] ?: 'walk_in',
                    'status' => $row['status'] ?: 'active',
                    'updated_by' => $userId,
                    'created_by' => $userId,
                ]
            );

            return;
        }

        if ($module === 'suppliers') {
            Supplier::query()->updateOrCreate(
                ['supplier_code' => $row['supplier_code']],
                [
                    'supplier_name' => $row['supplier_name'],
                    'contact_person' => $row['contact_person'] ?: null,
                    'contact_number' => $row['contact_number'] ?: null,
                    'email' => $row['email'] ?: null,
                    'address' => $row['address'] ?: null,
                    'payment_terms' => $row['payment_terms'] ?: null,
                    'status' => $row['status'] ?: 'active',
                ]
            );

            return;
        }

        if ($module === 'products') {
            $categoryId = ProductCategory::query()->where('category_code', $row['category_code'])->value('id');
            $brandId = Brand::query()->where('brand_code', $row['brand_code'])->value('id');

            if (! $categoryId || ! $brandId) {
                throw new \RuntimeException('Invalid category_code or brand_code mapping.');
            }

            Product::query()->updateOrCreate(
                ['sku' => $row['sku']],
                [
                    'product_code' => $row['product_code'],
                    'barcode' => $row['barcode'] ?: null,
                    'product_name' => $row['product_name'],
                    'category_id' => $categoryId,
                    'brand_id' => $brandId,
                    'cost_price' => (float) $row['cost_price'],
                    'selling_price' => (float) $row['selling_price'],
                    'reorder_level' => (int) ($row['reorder_level'] ?: 0),
                    'status' => $row['status'] ?: 'active',
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]
            );

            return;
        }

        throw new \RuntimeException('Unsupported import module.');
    }

    private function storeRejectedRows(DataImportLog $log, array $errors): string
    {
        $path = 'imports/rejected/'.$log->import_number.'-rejected.csv';
        $lines = ['row_number,error_messages,row_payload_json'];

        foreach ($errors as $error) {
            $line = [
                $error['row_number'],
                '"'.str_replace('"', '""', implode(' | ', $error['error_messages'])).'"',
                '"'.str_replace('"', '""', json_encode($error['row_payload'])).'"',
            ];
            $lines[] = implode(',', $line);
        }

        Storage::put($path, implode("\n", $lines)."\n");

        return $path;
    }
}
