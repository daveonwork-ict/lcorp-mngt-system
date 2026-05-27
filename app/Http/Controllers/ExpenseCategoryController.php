<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseCategoryController extends Controller
{
    public function index(): View
    {
        return view('finance.expense-categories.index', [
            'categories' => ExpenseCategory::query()->orderBy('category_name')->paginate(20),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_code' => ['required', 'string', 'max:50', 'unique:expense_categories,category_code'],
            'category_name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
            'requires_approval' => ['nullable', 'boolean'],
            'receipt_required' => ['nullable', 'boolean'],
            'monthly_budget_limit' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        ExpenseCategory::query()->create([
            'category_code' => strtoupper($validated['category_code']),
            'category_name' => $validated['category_name'],
            'description' => $validated['description'] ?? null,
            'requires_approval' => (bool) ($validated['requires_approval'] ?? false),
            'receipt_required' => (bool) ($validated['receipt_required'] ?? false),
            'monthly_budget_limit' => $validated['monthly_budget_limit'] ?? null,
            'status' => $validated['status'],
        ]);

        return back()->with('status', 'Expense category created.');
    }

    public function update(Request $request, ExpenseCategory $category): RedirectResponse
    {
        $validated = $request->validate([
            'category_name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
            'requires_approval' => ['nullable', 'boolean'],
            'receipt_required' => ['nullable', 'boolean'],
            'monthly_budget_limit' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $category->update([
            'category_name' => $validated['category_name'],
            'description' => $validated['description'] ?? null,
            'requires_approval' => (bool) ($validated['requires_approval'] ?? false),
            'receipt_required' => (bool) ($validated['receipt_required'] ?? false),
            'monthly_budget_limit' => $validated['monthly_budget_limit'] ?? null,
            'status' => $validated['status'],
        ]);

        return back()->with('status', 'Expense category updated.');
    }
}
