@php
    $notificationService = app(\App\Services\NotificationService::class);
    $isCommAllowed = auth()->check() && auth()->user()->hasPermission('view_notification_center');
    $unreadCount = auth()->check()
        ? ($isCommAllowed ? $notificationService->communicationUnreadCount(auth()->user()) : $notificationService->unreadCount(auth()->id()))
        : 0;
    $notificationRoute = $isCommAllowed ? route('communication.notifications.index') : route('admin.notifications.index');
@endphp
<a class="nav-link" href="{{ $notificationRoute }}" role="button" aria-label="Notifications">
    <i class="far fa-bell"></i>
    @if ($unreadCount > 0)
        <span class="badge badge-warning navbar-badge">{{ $unreadCount }}</span>
    @endif
</a>
