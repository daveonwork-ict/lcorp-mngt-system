<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\View\View;

class CustomerProfileController extends Controller
{
    public function show(Customer $customer): View
    {
        $customer->load([
            'sales.branch',
            'sales.items.product',
            'sales.payments.paymentMethod',
            'warranties.product.brand',
            'warrantyClaims.warranty.product',
            'notes.creator',
        ]);

        return view('customers.profile', [
            'customer' => $customer,
            'totalPurchases' => (float) $customer->sales->sum('total_amount'),
            'lastPurchaseDate' => optional($customer->sales->sortByDesc('sales_date')->first()?->sales_date)->format('Y-m-d'),
        ]);
    }
}
