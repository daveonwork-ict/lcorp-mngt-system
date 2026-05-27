<?php

namespace App\Http\Controllers;

use App\Models\CommunicationNotification;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationCenterController extends Controller
{
    public function __construct(private readonly NotificationService $notificationService)
    {
    }

    public function index(Request $request): View
    {
        if (! $request->user()->hasPermission('view_notification_center') && $request->user()->role?->code !== config('rms.owner_role_code')) {
            abort(403, 'Notification center access denied.');
        }

        return view('communication.notifications.index', [
            'notifications' => $this->notificationService->paginateCommunicationForUser($request->user()),
        ]);
    }

    public function markRead(Request $request, CommunicationNotification $notification): RedirectResponse
    {
        $this->notificationService->markCommunicationRead($notification, $request->user());

        return back()->with('status', 'Notification marked as read.');
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $this->notificationService->markAllCommunicationRead($request->user());

        return back()->with('status', 'All communication notifications marked as read.');
    }
}
