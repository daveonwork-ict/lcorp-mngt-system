<?php

namespace App\Http\Controllers;

use App\Models\UserSession;
use App\Services\SessionSecurityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SessionSecurityController extends Controller
{
    public function __construct(private readonly SessionSecurityService $service)
    {
    }

    public function index(Request $request): View
    {
        return view('admin.security.sessions', [
            'sessions' => $this->service->activeSessions($request->only(['user_id', 'status'])),
            'filters' => $request->only(['user_id', 'status']),
        ]);
    }

    public function terminate(UserSession $userSession): RedirectResponse
    {
        $this->service->terminate($userSession);

        return back()->with('status', 'Session terminated.');
    }

    public function terminateOthers(): RedirectResponse
    {
        $count = $this->service->terminateOtherSessionsForCurrentUser();

        return back()->with('status', $count.' other sessions terminated.');
    }
}
