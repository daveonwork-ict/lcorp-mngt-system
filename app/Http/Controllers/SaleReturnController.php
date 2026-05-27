<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleReturn;
use App\Services\SaleReturnService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SaleReturnController extends Controller
{
    public function __construct(private readonly SaleReturnService $saleReturnService)
    {
    }

    public function index(): View
    {
        return view('sales.returns', [
            'returns' => SaleReturn::query()->with(['sale.branch', 'requester', 'approver'])->latest('id')->paginate(20),
            'sales' => Sale::query()->latest('id')->limit(200)->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sale_id' => ['required', 'exists:sales,id'],
            'return_type' => ['required', 'in:return,exchange'],
            'reason' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.sale_item_id' => ['required', 'exists:sale_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.item_condition' => ['nullable', 'string'],
            'items.*.remarks' => ['nullable', 'string'],
        ]);

        $saleReturn = $this->saleReturnService->request($validated);

        return back()->with('status', 'Return request submitted: '.$saleReturn->return_number);
    }

    public function approve(SaleReturn $saleReturn): RedirectResponse
    {
        $this->saleReturnService->approve($saleReturn);

        return back()->with('status', 'Return request approved.');
    }

    public function reject(SaleReturn $saleReturn): RedirectResponse
    {
        $this->saleReturnService->reject($saleReturn);

        return back()->with('status', 'Return request rejected.');
    }
}
