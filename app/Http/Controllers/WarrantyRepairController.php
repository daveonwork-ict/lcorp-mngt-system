<?php

namespace App\Http\Controllers;

use App\Models\WarrantyClaim;
use App\Services\WarrantyRepairService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WarrantyRepairController extends Controller
{
    public function __construct(private readonly WarrantyRepairService $repairService)
    {
    }

    public function store(Request $request, WarrantyClaim $claim): RedirectResponse
    {
        $validated = $request->validate([
            'repair_details' => ['nullable', 'string'],
            'technician_name' => ['nullable', 'string', 'max:190'],
            'repair_start_date' => ['nullable', 'date'],
            'repair_end_date' => ['nullable', 'date'],
            'repair_status' => ['required', 'in:pending,under_repair,completed'],
            'remarks' => ['nullable', 'string'],
        ]);

        $this->repairService->upsert($claim, $validated);

        return back()->with('status', 'Repair details saved.');
    }
}
