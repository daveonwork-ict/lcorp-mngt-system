<?php

namespace App\Http\Controllers;

use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Services\RoleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function __construct(private readonly RoleService $roleService)
    {
    }

    public function index(): View
    {
        return view('admin.roles.index', [
            'roles' => $this->roleService->paginate(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.roles.form', [
            'role' => new Role(),
            'permissions' => Permission::query()->orderBy('module')->orderBy('name')->get()->groupBy('module'),
            'mode' => 'create',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $role = $this->roleService->create($request->validated());
        $this->roleService->syncPermissions($role, $request->validated('permission_ids', []));

        return redirect()->route('admin.roles.show', $role)->with('status', 'Role created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role): View
    {
        $role->load(['permissions', 'users']);

        return view('admin.roles.show', ['role' => $role]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role): View
    {
        $role->load('permissions');

        return view('admin.roles.form', [
            'role' => $role,
            'permissions' => Permission::query()->orderBy('module')->orderBy('name')->get()->groupBy('module'),
            'mode' => 'edit',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $this->roleService->update($role, $request->validated());
        $this->roleService->syncPermissions($role, $request->validated('permission_ids', []));

        return redirect()->route('admin.roles.show', $role)->with('status', 'Role updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role): RedirectResponse
    {
        $this->roleService->toggleStatus($role, 'inactive');

        return back()->with('status', 'Role deactivated.');
    }

    public function updateStatus(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:active,inactive'],
        ]);

        $this->roleService->toggleStatus($role, $validated['status']);

        return back()->with('status', 'Role status changed.');
    }
}
