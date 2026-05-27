<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PrototypeController extends Controller
{
    public function ownerDashboard(): View
    {
        return view('dashboard.owner', [
            'metrics' => config('rms.owner_metrics'),
            'charts' => config('rms.owner_charts'),
            'tables' => config('rms.owner_tables'),
        ]);
    }

    public function branchDashboard(): View
    {
        return view('dashboard.branch', [
            'metrics' => config('rms.branch_metrics'),
        ]);
    }

    public function module(string $module): View
    {
        $modules = collect(config('rms.modules'));
        $meta = $modules->firstWhere('slug', $module);

        abort_unless($meta, 404);

        return view('prototypes.module', [
            'meta' => $meta,
            'filters' => ['Date Range', 'Branch', 'Status'],
            'actions' => ['Export', 'Print'],
        ]);
    }

    public function switchBranch(Request $request): RedirectResponse
    {
        $request->validate([
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
        ]);

        $request->session()->put('active_branch_id', $request->integer('branch_id'));

        return back();
    }
}
