<?php

namespace App\Http\Controllers;

use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PermissionController extends Controller
{
    public function __construct(private readonly PermissionService $permissionService)
    {
    }

    public function index(): View
    {
        return view('admin.permissions.index', [
            'groupedPermissions' => $this->permissionService->grouped(),
        ]);
    }
}
