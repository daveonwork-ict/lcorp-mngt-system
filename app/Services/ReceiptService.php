<?php

namespace App\Services;

use App\Models\ReceiptSetting;
use App\Models\Sale;

class ReceiptService
{
    public function __construct(private readonly SalesAuditService $salesAuditService)
    {
    }

    public function data(Sale $sale): array
    {
        $sale->load(['branch', 'cashier', 'items.product', 'items.imei', 'payments.paymentMethod']);

        $setting = ReceiptSetting::query()->where('branch_id', $sale->branch_id)->first()
            ?? ReceiptSetting::query()->whereNull('branch_id')->first();

        return [
            'sale' => $sale,
            'setting' => $setting,
        ];
    }

    public function logPrint(Sale $sale, bool $reprint = false): void
    {
        $this->salesAuditService->log(
            $reprint ? 'receipt_reprinted' : 'receipt_printed',
            [],
            ['sale_id' => $sale->id],
            $sale->branch_id,
            $reprint ? 'Receipt reprinted' : 'Receipt printed'
        );
    }
}
