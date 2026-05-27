<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\User;
use App\Services\StaffAccountabilityService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StaffAccountabilityController extends Controller
{
    public function __construct(private readonly StaffAccountabilityService $service)
    {
    }

    public function index(Request $request): View
    {
        return view('purchasing.accountabilities.index', [
            'records' => $this->service->paginate($request->only(['branch_id', 'employee_id', 'date_from', 'date_to'])),
            'branches' => Branch::query()->where('is_active', true)->orderBy('name')->get(),
            'users' => User::query()->orderBy('full_name')->get(),
            'filters' => $request->only(['branch_id', 'employee_id', 'date_from', 'date_to']),
        ]);
    }
}
