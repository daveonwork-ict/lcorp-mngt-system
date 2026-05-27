<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(private readonly NotificationService $notificationService)
    {
    }

    public function index(): View
    {
        return view('admin.security.notifications', [
            'notifications' => $this->notificationService->paginateForUser(auth()->id()),
        ]);
    }

    public function markRead(Notification $notification): RedirectResponse
    {
        $this->notificationService->markRead($notification);

        return back();
    }

    public function markAllRead(): RedirectResponse
    {
        $this->notificationService->markAllRead(auth()->id());

        return back()->with('status', 'All notifications marked as read.');
    }
}
