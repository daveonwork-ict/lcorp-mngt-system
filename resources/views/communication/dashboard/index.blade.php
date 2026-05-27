@extends('layouts.app')

@section('page_title', 'Communication Dashboard')
@section('content')
<div class="row">
    <div class="col-md-3 col-6 mb-3"><div class="small-box bg-info"><div class="inner"><h3>{{ $activeAnnouncements }}</h3><p>Active Announcements</p></div><div class="icon"><i class="fas fa-bullhorn"></i></div></div></div>
    <div class="col-md-3 col-6 mb-3"><div class="small-box bg-danger"><div class="inner"><h3>{{ $urgentAnnouncements }}</h3><p>Urgent Announcements</p></div><div class="icon"><i class="fas fa-exclamation-triangle"></i></div></div></div>
    <div class="col-md-3 col-6 mb-3"><div class="small-box bg-warning"><div class="inner"><h3>{{ $unreadAnnouncements }}</h3><p>Unread Announcements</p></div><div class="icon"><i class="fas fa-envelope-open-text"></i></div></div></div>
    <div class="col-md-3 col-6 mb-3"><div class="small-box bg-primary"><div class="inner"><h3>{{ $activeChatRooms }}</h3><p>Active Chat Rooms</p></div><div class="icon"><i class="fas fa-comments"></i></div></div></div>
</div>

<div class="row">
    <div class="col-md-4 col-6 mb-3"><div class="small-box bg-secondary"><div class="inner"><h3>{{ $unreadMessages }}</h3><p>Unread Messages</p></div><div class="icon"><i class="fas fa-comment-dots"></i></div></div></div>
    <div class="col-md-4 col-6 mb-3"><div class="small-box bg-success"><div class="inner"><h3>{{ $pendingAcknowledgments }}</h3><p>Pending Acknowledgments</p></div><div class="icon"><i class="fas fa-check-double"></i></div></div></div>
    <div class="col-md-4 col-12 mb-3"><div class="small-box bg-maroon"><div class="inner"><h3>{{ $notificationUnread }}</h3><p>Unread Notifications</p></div><div class="icon"><i class="fas fa-bell"></i></div></div></div>
</div>

<div class="row">
    <div class="col-lg-6 mb-3">
        <div class="card h-100">
            <div class="card-header"><strong>Latest Announcements</strong></div>
            <div class="card-body table-responsive p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Title</th><th>Priority</th><th>Posted By</th><th>When</th></tr></thead>
                    <tbody>
                    @forelse($recentAnnouncements as $announcement)
                        <tr>
                            <td><a href="{{ route('announcements.show', $announcement) }}">{{ $announcement->title }}</a></td>
                            <td><span class="badge badge-{{ in_array($announcement->priority_level, ['urgent','critical']) ? 'danger' : 'secondary' }}">{{ ucfirst($announcement->priority_level) }}</span></td>
                            <td>{{ $announcement->creator?->display_name }}</td>
                            <td>{{ $announcement->published_at?->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted">No announcements.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-3">
        <div class="card h-100">
            <div class="card-header"><strong>Recent Messages</strong></div>
            <div class="card-body table-responsive p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Room</th><th>Sender</th><th>Message</th><th>When</th></tr></thead>
                    <tbody>
                    @forelse($recentMessages as $message)
                        <tr>
                            <td><a href="{{ route('chat.rooms.show', $message->room) }}">{{ $message->room?->room_name }}</a></td>
                            <td>{{ $message->sender?->display_name }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($message->message_body ?? '[attachment]', 45) }}</td>
                            <td>{{ $message->created_at?->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted">No messages yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
