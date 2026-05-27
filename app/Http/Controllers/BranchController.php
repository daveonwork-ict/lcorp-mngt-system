<?php

namespace App\Http\Controllers;

use App\Http\Requests\Branch\StoreBranchRequest;
use App\Http\Requests\Branch\UpdateBranchRequest;
use App\Models\Branch;
use App\Models\User;
use App\Services\BranchService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BranchController extends Controller
{
    public function __construct(private readonly BranchService $branchService)
    {
    }

    public function index(Request $request): View
    {
        return view('admin.branches.index', [
            'branches' => $this->branchService->paginate($request->only('search')),
            'filters' => $request->only('search'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.branches.form', [
            'branch' => new Branch(),
            'users' => User::query()->where('is_active', true)->orderBy('full_name')->get(),
            'mode' => 'create',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBranchRequest $request): RedirectResponse
    {
        $branch = $this->branchService->create($request->validated());
        $this->branchService->assignUsers($branch, $request->validated('user_ids', []), $request->integer('manager_id'));

        return redirect()->route('admin.branches.show', $branch)->with('status', 'Branch created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Branch $branch): View
    {
        $branch->load(['manager', 'users']);

        return view('admin.branches.show', ['branch' => $branch]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Branch $branch): View
    {
        $branch->load('users');

        return view('admin.branches.form', [
            'branch' => $branch,
            'users' => User::query()->where('is_active', true)->orderBy('full_name')->get(),
            'mode' => 'edit',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBranchRequest $request, Branch $branch): RedirectResponse
    {
        $this->branchService->update($branch, $request->validated());
        $this->branchService->assignUsers($branch, $request->validated('user_ids', []), $request->integer('manager_id'));

        return redirect()->route('admin.branches.show', $branch)->with('status', 'Branch updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Branch $branch): RedirectResponse
    {
        $this->branchService->toggleStatus($branch, 'inactive');

        return back()->with('status', 'Branch deactivated.');
    }

    public function updateStatus(Request $request, Branch $branch): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:active,inactive,maintenance,closed'],
        ]);

        $this->branchService->toggleStatus($branch, $validated['status']);

        return back()->with('status', 'Branch status changed.');
    }
}
