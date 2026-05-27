<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\CustomerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function __construct(private readonly CustomerService $customerService)
    {
    }

    public function index(Request $request): View
    {
        return view('customers.index', [
            'customers' => $this->customerService->paginate($request->only(['search', 'status'])),
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:120'],
            'middle_name' => ['nullable', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'suffix' => ['nullable', 'string', 'max:50'],
            'mobile_number' => ['required', 'regex:/^[0-9+\-\s]{7,20}$/'],
            'email' => ['nullable', 'email'],
            'address' => ['nullable', 'string'],
            'birthdate' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:male,female,other,prefer_not_to_say'],
            'customer_type' => ['required', 'in:walk_in,regular,vip,corporate,online_customer'],
            'status' => ['required', 'in:active,inactive,blocklisted'],
        ]);

        $this->customerService->create($validated);

        return back()->with('status', 'Customer created.');
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:120'],
            'middle_name' => ['nullable', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'suffix' => ['nullable', 'string', 'max:50'],
            'mobile_number' => ['required', 'regex:/^[0-9+\-\s]{7,20}$/'],
            'email' => ['nullable', 'email'],
            'address' => ['nullable', 'string'],
            'birthdate' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:male,female,other,prefer_not_to_say'],
            'customer_type' => ['required', 'in:walk_in,regular,vip,corporate,online_customer'],
            'status' => ['required', 'in:active,inactive,blocklisted'],
        ]);

        $this->customerService->update($customer, $validated);

        return back()->with('status', 'Customer updated.');
    }

    public function deactivate(Customer $customer): RedirectResponse
    {
        $this->customerService->deactivate($customer);

        return back()->with('status', 'Customer deactivated.');
    }
}
