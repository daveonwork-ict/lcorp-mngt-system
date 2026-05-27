<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Services\ReceiptService;
use App\Services\SalesService;
use Illuminate\View\View;

class ReceiptController extends Controller
{
    public function __construct(
        private readonly ReceiptService $receiptService,
        private readonly SalesService $salesService,
    ) {
    }

    public function show(Sale $sale): View
    {
        $this->salesService->ensureCanAccessSale($sale);

        $data = $this->receiptService->data($sale);
        $this->receiptService->logPrint($sale, false);

        return view('sales.receipt', $data);
    }

    public function reprint(Sale $sale): View
    {
        abort_unless(auth()->user()?->hasPermission('reprint_receipt'), 403);

        $this->salesService->ensureCanAccessSale($sale);

        $data = $this->receiptService->data($sale);
        $this->receiptService->logPrint($sale, true);

        return view('sales.receipt', $data);
    }
}
