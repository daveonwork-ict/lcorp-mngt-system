@extends('layouts.app')

@section('page_title', 'Chat Rooms')
@section('content')
@if(auth()->user()->hasPermission('create_chat_room'))
<div class="card mb-3">
    <div class="card-header">Create Chat Room</div>
    <div class="card-body">
        <form method="POST" action="{{ route('chat.rooms.store') }}" class="form-row">@csrf
            <div class="col-md-4 mb-2"><input class="form-control" name="room_name" placeholder="Room name" required></div>
            <div class="col-md-3 mb-2"><select class="form-control" name="room_type" required>@foreach(['private','branch','management','inventory','accounting','operations','custom'] as $type)<option value="{{ $type }}">{{ ucfirst($type) }}</option>@endforeach</select></div>
            <div class="col-md-2 mb-2"><input class="form-control" name="branch_id" placeholder="Branch ID"></div>
            <div class="col-md-3 mb-2"><button class="btn btn-primary btn-block">Create</button></div>
            <div class="col-md-12 mb-2"><small class="text-muted">Add members after creating room or include in member management page.</small></div>
        </form>
    </div>
</div>
@endif

<div class="card">
    <div class="card-body table-responsive p-0">
        <table class="table table-sm mb-0">
            <thead><tr><th>Room</th><th>Type</th><th>Branch</th><th>Status</th><th>Members</th><th></th></tr></thead>
            <tbody>
            @forelse($rooms as $room)
                <tr>
                    <td><a href="{{ route('chat.rooms.show', $room) }}">{{ $room->room_name }}</a></td>
                    <td>{{ ucfirst($room->room_type) }}</td>
                    <td>{{ $room->branch?->branch_name ?? '-' }}</td>
                    <td><span class="badge badge-{{ $room->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($room->status) }}</span></td>
                    <td>{{ $room->members->where('status', 'active')->count() }}</td>
                    <td><a class="btn btn-xs btn-outline-dark" href="{{ route('chat.rooms.members.index', $room) }}">Members</a></td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted">No chat rooms found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $rooms->links() }}</div>
</div>
@endsection
