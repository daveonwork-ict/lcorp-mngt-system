<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\PaymentMethod;
use App\Services\ExpenseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function __construct(private readonly ExpenseService $expenseService)
    {
    }

    public function index(Request $request): View
    {
        return view('finance.expenses.index', [
            'expenses' => $this->expenseService->paginate($request->only(['branch_id', 'status', 'category_id', 'date_from', 'date_to'])),
            'branches' => Branch::query()->where('is_active', true)->orderBy('name')->get(),
            'categories' => ExpenseCategory::query()->where('status', 'active')->orderBy('category_name')->get(),
            'paymentMethods' => PaymentMethod::query()->where('status', 'active')->orderBy('payment_method_name')->get(),
            'filters' => $request->only(['branch_id', 'status', 'category_id', 'date_from', 'date_to']),
        ]);
    }

    public function show(Expense $expense): View
    {
        return view('finance.expenses.show', [
            'expense' => $expense->load(['branch', 'category', 'submitter', 'approver', 'attachments']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'category_id' => ['required', 'exists:expense_categories,id'],
            'expense_date' => ['required', 'date'],
            'vendor_or_payee' => ['required', 'string', 'max:150'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method_id' => ['nullable', 'exists:payment_methods,id'],
            'description' => ['nullable', 'string'],
            'remarks' => ['nullable', 'string'],
            'status' => ['nullable', 'in:draft,pending'],
            'receipt_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ]);

        $category = ExpenseCategory::query()->findOrFail($validated['category_id']);

        if ($category->receipt_required && ! $request->hasFile('receipt_file')) {
            return back()->withErrors(['receipt_file' => 'Receipt is required for this category.'])->withInput();
        }

        $expense = $this->expenseService->create($validated + [
            'status' => $category->requires_approval ? 'pending' : 'approved',
        ]);

        if ($request->hasFile('receipt_file')) {
            $file = $request->file('receipt_file');
            $path = $file->store('expenses/receipts');
            $this->expenseService->addAttachment($expense, [
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $file->getClientMimeType() ?: $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);
        }

        return back()->with('status', 'Expense submitted.');
    }
}
