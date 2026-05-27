<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserBranchController extends Controller
{
    public function __construct(private readonly UserService $userService)
    {
    }

    public function edit(User $user): View
    {
        return view('admin.users.branches', [
            'user' => $user->load('branches'),
            'branches' => \App\Models\Branch::query()->where('is_active', true)->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'branch_ids' => ['required', 'array'],
            'branch_ids.*' => ['integer', 'exists:branches,id'],
            'primary_branch_id' => ['nullable', 'integer', 'exists:branches,id'],
        ]);

        $this->userService->syncBranches($user, $validated['branch_ids'], $validated['primary_branch_id'] ?? null);

        return redirect()->route('admin.users.show', $user)->with('status', 'User branch assignment updated.');
    }
}
