@extends('layouts.app')

@section('page_title', 'Chat Conversation')
@section('content')
<div class="row">
    <div class="col-lg-4 mb-3">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>{{ $room->room_name }}</strong>
                <a href="{{ route('chat.rooms.members.index', $room) }}" class="btn btn-xs btn-outline-secondary">Members</a>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @foreach($room->members as $member)
                        @if($member->status === 'active')
                        <li class="list-group-item d-flex justify-content-between">
                            <span>{{ $member->user?->display_name }}</span>
                            <span class="badge badge-light">{{ ucfirst($member->role_in_room) }}</span>
                        </li>
                        @endif
                    @endforeach
                </ul>
            </div>
            <div class="card-footer small text-muted">Unread messages: {{ $unreadCount }}</div>
        </div>
    </div>

    <div class="col-lg-8 mb-3">
        <div class="card">
            <div class="card-header">Conversation</div>
            <div class="card-body" style="max-height: 480px; overflow-y: auto;">
                @forelse($room->messages->sortBy('id') as $message)
                    <div class="mb-3 p-2 border rounded {{ $message->sender_id === auth()->id() ? 'bg-light' : '' }}">
                        <div class="d-flex justify-content-between mb-1">
                            <strong>{{ $message->sender?->display_name }}</strong>
                            <span class="small text-muted">{{ $message->created_at?->format('Y-m-d H:i') }}</span>
                        </div>
                        @if($message->parent)
                            <div class="small text-muted">Reply to: {{ \Illuminate\Support\Str::limit($message->parent->message_body, 60) }}</div>
                        @endif
                        <div>{{ $message->status === 'deleted' ? '[Deleted message]' : $message->message_body }}</div>

                        @if($message->attachments->isNotEmpty())
                            <div class="mt-2">
                                @foreach($message->attachments as $attachment)
                                    <a class="btn btn-xs btn-outline-secondary mb-1" href="{{ route('chat.attachments.download', $attachment) }}">{{ $attachment->file_name }}</a>
                                @endforeach
                            </div>
                        @endif

                        <div class="mt-2">
                            @if($message->sender_id === auth()->id() || auth()->user()->hasPermission('edit_chat_message'))
                            <form method="POST" action="{{ route('chat.messages.update', $message) }}" class="form-inline d-inline">@csrf @method('PUT')
                                <input class="form-control form-control-sm mr-1" name="message_body" value="{{ $message->message_body }}" required>
                                <button class="btn btn-xs btn-outline-primary">Edit</button>
                            </form>
                            @endif
                            @if($message->sender_id === auth()->id() || auth()->user()->hasPermission('delete_chat_message'))
                            <form method="POST" action="{{ route('chat.messages.destroy', $message) }}" class="d-inline">@csrf @method('DELETE')<button class="btn btn-xs btn-outline-danger">Delete</button></form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted">No messages yet.</div>
                @endforelse
            </div>
            <div class="card-footer">
                <form method="POST" action="{{ route('chat.messages.store', $room) }}" enctype="multipart/form-data" class="form-row">@csrf
                    <div class="col-md-7 mb-2"><input class="form-control" name="message_body" placeholder="Type your message... Use @username for mentions."></div>
                    <div class="col-md-3 mb-2"><input class="form-control" type="file" name="attachment"></div>
                    <div class="col-md-2 mb-2"><button class="btn btn-primary btn-block">Send</button></div>
                </form>
                <form method="POST" action="{{ route('chat.rooms.read.mark', $room) }}" class="mt-2">@csrf<button class="btn btn-sm btn-outline-success">Mark Room Read</button></form>
            </div>
        </div>
    </div>
</div>
@endsection
