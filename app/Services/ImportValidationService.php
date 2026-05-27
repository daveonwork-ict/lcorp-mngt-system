<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\ProductCategory;

class ImportValidationService
{
    public function supportedModules(): array
    {
        return [
            'customers' => [
                'label' => 'Customers',
                'headers' => ['customer_code', 'first_name', 'last_name', 'mobile_number', 'email', 'address', 'customer_type', 'status'],
            ],
            'suppliers' => [
                'label' => 'Suppliers',
                'headers' => ['supplier_code', 'supplier_name', 'contact_person', 'contact_number', 'email', 'address', 'payment_terms', 'status'],
            ],
            'products' => [
                'label' => 'Products',
                'headers' => ['product_code', 'sku', 'barcode', 'product_name', 'category_code', 'brand_code', 'cost_price', 'selling_price', 'reorder_level', 'status'],
            ],
        ];
    }

    public function headersFor(string $module): array
    {
        return $this->supportedModules()[$module]['headers'] ?? [];
    }

    public function validateRows(string $module, array $rows): array
    {
        $errors = [];
        $validRows = [];
        $seenKeys = [];
        $categoryCodes = ProductCategory::query()->pluck('id', 'category_code')->all();
        $brandCodes = Brand::query()->pluck('id', 'brand_code')->all();

        foreach ($rows as $row) {
            $lineErrors = [];
            $payload = $row['data'];

            if ($module === 'customers') {
                $this->requireFields($payload, ['customer_code', 'first_name', 'last_name', 'mobile_number'], $lineErrors);
                $this->validateDuplicateInFile($payload['customer_code'] ?? null, $seenKeys, 'customer_code', $lineErrors);
                $payload['customer_type'] = $payload['customer_type'] ?: 'walk_in';
                $payload['status'] = $payload['status'] ?: 'active';
            }

            if ($module === 'suppliers') {
                $this->requireFields($payload, ['supplier_code', 'supplier_name'], $lineErrors);
                $this->validateDuplicateInFile($payload['supplier_code'] ?? null, $seenKeys, 'supplier_code', $lineErrors);
                $payload['status'] = $payload['status'] ?: 'active';
            }

            if ($module === 'products') {
                $this->requireFields($payload, ['product_code', 'sku', 'product_name', 'category_code', 'brand_code', 'cost_price', 'selling_price'], $lineErrors);
                $this->validateDuplicateInFile($payload['sku'] ?? null, $seenKeys, 'sku', $lineErrors);

                if (! is_numeric((string) ($payload['cost_price'] ?? null))) {
                    $lineErrors[] = 'cost_price must be numeric.';
                }
                if (! is_numeric((string) ($payload['selling_price'] ?? null))) {
                    $lineErrors[] = 'selling_price must be numeric.';
                }
                if (($payload['reorder_level'] ?? '') !== '' && ! ctype_digit((string) $payload['reorder_level'])) {
                    $lineErrors[] = 'reorder_level must be a whole number.';
                }

                if (! isset($categoryCodes[$payload['category_code'] ?? ''])) {
                    $lineErrors[] = 'category_code not found.';
                }
                if (! isset($brandCodes[$payload['brand_code'] ?? ''])) {
                    $lineErrors[] = 'brand_code not found.';
                }

                $payload['status'] = $payload['status'] ?: 'active';
            }

            if ($lineErrors !== []) {
                $errors[] = [
                    'row_number' => $row['row_number'],
                    'row_payload' => $payload,
                    'error_messages' => $lineErrors,
                ];
                continue;
            }

            $validRows[] = [
                'row_number' => $row['row_number'],
                'data' => $payload,
            ];
        }

        return [
            'valid_rows' => $validRows,
            'errors' => $errors,
        ];
    }

    private function requireFields(array $payload, array $fields, array &$errors): void
    {
        foreach ($fields as $field) {
            if (($payload[$field] ?? '') === '') {
                $errors[] = $field.' is required.';
            }
        }
    }

    private function validateDuplicateInFile(?string $value, array &$seenKeys, string $field, array &$errors): void
    {
        if (! $value) {
            return;
        }

        $key = strtolower(trim($value));
        if (isset($seenKeys[$field][$key])) {
            $errors[] = $field.' is duplicated in file.';
            return;
        }

        $seenKeys[$field][$key] = true;
    }
}
