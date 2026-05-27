<?php

namespace App\Services;

use App\Models\ReportExport;

class ReportExportService
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function record(string $reportType, string $format, array $filters = [], ?int $branchId = null, ?string $fileName = null): ReportExport
    {
        $export = ReportExport::query()->create([
            'user_id' => auth()->id(),
            'branch_id' => $branchId,
            'report_type' => $reportType,
            'export_format' => $format,
            'file_name' => $fileName,
            'filters_used' => $filters ?: null,
            'status' => 'generated',
            'generated_at' => now(),
        ]);

        $this->auditLogService->record(
            'reports',
            'report_exported',
            [],
            [
                'report_type' => $reportType,
                'format' => $format,
                'filters_used' => $filters,
                'export_id' => $export->id,
            ],
            $branchId,
            'Report exported: '.$reportType.' ('.$format.')'
        );

        return $export;
    }

    public function toCsv(array $headers, iterable $rows): string
    {
        $content = implode(',', $headers)."\n";

        foreach ($rows as $row) {
            $encoded = array_map(function ($value): string {
                $text = str_replace('"', '""', (string) $value);
                return '"'.$text.'"';
            }, $row);

            $content .= implode(',', $encoded)."\n";
        }

        return $content;
    }

    public function toExcelTsv(array $headers, iterable $rows): string
    {
        $content = implode("\t", $headers)."\n";

        foreach ($rows as $row) {
            $content .= implode("\t", array_map(fn ($v) => str_replace(["\t", "\n", "\r"], ' ', (string) $v), $row))."\n";
        }

        return $content;
    }
}
