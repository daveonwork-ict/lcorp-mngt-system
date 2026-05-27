@extends('layouts.app')

@section('page_title', 'Notification Center')
@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard.owner') }}">Dashboard</a></li>
<li class="breadcrumb-item active">Notifications</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header text-right">
        <form action="{{ route('admin.notifications.read-all') }}" method="POST" class="d-inline">@csrf <button class="btn btn-sm btn-outline-primary">Mark All Read</button></form>
    </div>
    <div class="card-body p-0">
        <ul class="list-group list-group-flush">
            @forelse($notifications as $notification)
                <li class="list-group-item d-flex justify-content-between align-items-center {{ $notification->is_read ? 'text-muted' : '' }}">
                    <div>
                        <strong>{{ $notification->title }}</strong>
                        <div class="small">{{ $notification->message }}</div>
                        <div class="small text-muted">{{ $notification->created_at?->format('Y-m-d H:i') }}</div>
                    </div>
                    @if(! $notification->is_read)
                        <form method="POST" action="{{ route('admin.notifications.read', $notification) }}">@csrf<button class="btn btn-xs btn-outline-success">Mark Read</button></form>
                    @endif
                </li>
            @empty
                <li class="list-group-item text-center">No notifications.</li>
            @endforelse
        </ul>
    </div>
    <div class="card-footer">{{ $notifications->links() }}</div>
</div>
@endsection
