<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentMethodController extends Controller
{
    public function index(): View
    {
        return view('sales.payment-methods', [
            'methods' => PaymentMethod::query()->orderBy('payment_method_name')->paginate(20),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'payment_method_name' => ['required', 'string', 'max:120', 'unique:payment_methods,payment_method_name'],
            'payment_type' => ['required', 'in:Cash,E-Wallet,Bank,Card,Other'],
            'requires_reference' => ['nullable', 'boolean'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $validated['requires_reference'] = $request->boolean('requires_reference');
        PaymentMethod::query()->create($validated);

        return back()->with('status', 'Payment method created.');
    }

    public function update(Request $request, PaymentMethod $paymentMethod): RedirectResponse
    {
        $validated = $request->validate([
            'payment_method_name' => ['required', 'string', 'max:120', 'unique:payment_methods,payment_method_name,'.$paymentMethod->id],
            'payment_type' => ['required', 'in:Cash,E-Wallet,Bank,Card,Other'],
            'requires_reference' => ['nullable', 'boolean'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $validated['requires_reference'] = $request->boolean('requires_reference');
        $paymentMethod->update($validated);

        return back()->with('status', 'Payment method updated.');
    }
}
