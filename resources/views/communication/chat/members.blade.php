@extends('layouts.app')

@section('page_title', 'Chat Members')
@section('content')
<div class="card mb-3">
    <div class="card-header">{{ $room->room_name }} Members</div>
    <div class="card-body">
        <form method="POST" action="{{ route('chat.rooms.members.store', $room) }}" class="form-row">@csrf
            <div class="col-md-6 mb-2">
                <select class="form-control" name="user_id" required>
                    <option value="">Select user</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->display_name }} ({{ $user->role?->name }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mb-2"><select class="form-control" name="role_in_room">@foreach(['admin','member','viewer'] as $role)<option value="{{ $role }}">{{ ucfirst($role) }}</option>@endforeach</select></div>
            <div class="col-md-3 mb-2"><button class="btn btn-primary btn-block">Add / Update</button></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body table-responsive p-0">
        <table class="table table-sm mb-0">
            <thead><tr><th>User</th><th>Role</th><th>Status</th><th>Joined</th><th></th></tr></thead>
            <tbody>
            @foreach($room->members as $member)
                <tr>
                    <td>{{ $member->user?->display_name }}</td>
                    <td>{{ ucfirst($member->role_in_room) }}</td>
                    <td>{{ ucfirst($member->status) }}</td>
                    <td>{{ $member->joined_at?->format('Y-m-d H:i') }}</td>
                    <td>
                        @if($member->status === 'active')
                        <form method="POST" action="{{ route('chat.rooms.members.destroy', [$room, $member->user_id]) }}">@csrf @method('DELETE')<button class="btn btn-xs btn-outline-danger">Remove</button></form>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
