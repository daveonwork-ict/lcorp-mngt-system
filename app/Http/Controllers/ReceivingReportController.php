<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\ReceivingReport;
use App\Services\ReceivingReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReceivingReportController extends Controller
{
    public function __construct(private readonly ReceivingReportService $service)
    {
    }

    public function index(Request $request): View
    {
        return view('purchasing.receiving-reports.index', [
            'reports' => $this->service->paginate($request->only(['branch_id', 'supplier_id'])),
            'orders' => PurchaseOrder::query()->whereIn('status', ['approved', 'sent', 'partial_received'])->with('items')->latest('id')->get(),
            'filters' => $request->only(['branch_id', 'supplier_id']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'purchase_order_id' => ['required', 'exists:purchase_orders,id'],
            'received_date' => ['required', 'date'],
            'delivery_receipt_number' => ['nullable', 'string', 'max:120'],
            'invoice_number' => ['nullable', 'string', 'max:120'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'remarks' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity_received' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['nullable', 'numeric', 'min:0'],
            'items.*.serialized_entries' => ['nullable', 'array'],
        ]);

        if ($request->hasFile('attachment')) {
            $validated['attachment_path'] = $request->file('attachment')->store('purchasing/receiving-documents');
        }

        $this->service->create($validated);

        return back()->with('status', 'Receiving report posted.');
    }

    public function download(ReceivingReport $receivingReport): StreamedResponse|RedirectResponse
    {
        if (! $receivingReport->attachment_path || ! Storage::exists($receivingReport->attachment_path)) {
            return back()->withErrors(['file' => 'Attachment file not found.']);
        }

        return Storage::download($receivingReport->attachment_path, basename($receivingReport->attachment_path));
    }
}
