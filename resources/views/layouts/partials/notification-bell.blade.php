@php
    $unreadCount = auth()->check() ? app(\App\Services\NotificationService::class)->unreadCount(auth()->id()) : 0;
@endphp
<a class="nav-link" href="{{ route('admin.notifications.index') }}" role="button" aria-label="Notifications">
    <i class="far fa-bell"></i>
    @if ($unreadCount > 0)
        <span class="badge badge-warning navbar-badge">{{ $unreadCount }}</span>
    @endif
</a>
