<?php

namespace App\Http\Controllers;

use App\Models\CashDenomination;
use App\Models\DailyClosing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CashDenominationController extends Controller
{
    public function store(Request $request, DailyClosing $closing): RedirectResponse
    {
        $validated = $request->validate([
            'denomination' => ['required', 'numeric', 'min:0.01'],
            'quantity' => ['required', 'integer', 'min:0'],
        ]);

        CashDenomination::query()->updateOrCreate(
            ['daily_closing_id' => $closing->id, 'denomination' => $validated['denomination']],
            [
                'quantity' => $validated['quantity'],
                'total_amount' => round((float) $validated['denomination'] * (int) $validated['quantity'], 2),
            ]
        );

        return back()->with('status', 'Denomination updated.');
    }
}
