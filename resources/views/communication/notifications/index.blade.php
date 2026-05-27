@extends('layouts.app')

@section('page_title', 'Communication Notification Center')
@section('content')
<div class="card">
    <div class="card-header text-right">
        <form method="POST" action="{{ route('communication.notifications.read-all') }}" class="d-inline">@csrf<button class="btn btn-sm btn-outline-primary">Mark All Read</button></form>
    </div>
    <div class="card-body p-0">
        <ul class="list-group list-group-flush">
            @forelse($notifications as $notification)
                <li class="list-group-item {{ $notification->is_read ? 'text-muted' : '' }}">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong>{{ $notification->title }}</strong>
                            <div class="small text-muted">{{ ucfirst(str_replace('_', ' ', $notification->category)) }}</div>
                            <div>{{ $notification->message }}</div>
                            <div class="small text-muted">{{ $notification->created_at?->format('Y-m-d H:i') }}</div>
                            @if(($notification->payload['route'] ?? null))
                                <a href="{{ $notification->payload['route'] }}" class="btn btn-xs btn-outline-secondary mt-1">Open</a>
                            @endif
                        </div>
                        @if(! $notification->is_read)
                            <form method="POST" action="{{ route('communication.notifications.read', $notification) }}">@csrf<button class="btn btn-xs btn-outline-success">Mark Read</button></form>
                        @endif
                    </div>
                </li>
            @empty
                <li class="list-group-item text-center text-muted">No communication notifications.</li>
            @endforelse
        </ul>
    </div>
    <div class="card-footer">{{ $notifications->links() }}</div>
</div>
@endsection
