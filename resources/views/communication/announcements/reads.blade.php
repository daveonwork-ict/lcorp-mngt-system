@extends('layouts.app')

@section('page_title', 'Announcement Read Tracking')
@section('content')
<div class="card mb-3">
    <div class="card-header">{{ $announcement->title }}</div>
    <div class="card-body">
        <form method="GET" class="form-row">
            <div class="col-md-4 mb-2"><input class="form-control" name="branch_id" value="{{ $filters['branch_id'] ?? '' }}" placeholder="Branch ID"></div>
            <div class="col-md-4 mb-2"><input class="form-control" name="role_id" value="{{ $filters['role_id'] ?? '' }}" placeholder="Role ID"></div>
            <div class="col-md-4 mb-2"><button class="btn btn-outline-primary btn-block">Filter</button></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body table-responsive p-0">
        <table class="table table-sm mb-0">
            <thead><tr><th>User</th><th>Role</th><th>Branch</th><th>Status</th><th>Read At</th></tr></thead>
            <tbody>
            @forelse($reads as $read)
                <tr>
                    <td>{{ $read->user?->display_name }}</td>
                    <td>{{ $read->user?->role?->name }}</td>
                    <td>{{ $read->branch?->branch_name }}</td>
                    <td><span class="badge badge-{{ $read->acknowledgment_status === 'acknowledged' ? 'success' : ($read->acknowledgment_status === 'read' ? 'info' : 'secondary') }}">{{ ucfirst($read->acknowledgment_status) }}</span></td>
                    <td>{{ $read->read_at?->format('Y-m-d H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted">No read records.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $reads->links() }}</div>
</div>
@endsection
