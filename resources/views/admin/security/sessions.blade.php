@extends('layouts.app')

@section('page_title', 'Active Sessions')
@section('content')
<div class="mb-3"><form method="POST" action="{{ route('admin.security.sessions.terminate-others') }}">@csrf<button class="btn btn-outline-danger btn-sm">Terminate My Other Sessions</button></form></div>
<div class="card"><div class="table-responsive p-0"><table class="table table-sm mb-0"><thead><tr><th>User</th><th>Session ID</th><th>IP</th><th>Last Activity</th><th>Status</th><th></th></tr></thead><tbody>@forelse($sessions as $session)<tr><td>{{ $session->user?->display_name }}</td><td>{{ $session->session_id }}</td><td>{{ $session->ip_address }}</td><td>{{ $session->last_activity_at }}</td><td>{{ ucfirst($session->status) }}</td><td>@if($session->status === 'active')<form method="POST" action="{{ route('admin.security.sessions.terminate', $session) }}">@csrf<button class="btn btn-sm btn-outline-danger">Terminate</button></form>@endif</td></tr>@empty<tr><td colspan="6" class="text-center text-muted">No sessions found.</td></tr>@endforelse</tbody></table></div><div class="card-footer">{{ $sessions->links() }}</div></div>
@endsection
