<?php

namespace App\Http\Controllers;

use App\Services\LoginSecurityService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoginSecurityController extends Controller
{
    public function __construct(private readonly LoginSecurityService $service)
    {
    }

    public function index(Request $request): View
    {
        return view('admin.security.login-activity', [
            'logs' => $this->service->history($request->only(['status', 'date_from', 'date_to'])),
            'filters' => $request->only(['status', 'date_from', 'date_to']),
        ]);
    }
}
