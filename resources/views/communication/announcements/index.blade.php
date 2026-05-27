@extends('layouts.app')

@section('page_title', 'Announcements')
@section('content')
<div class="card mb-3">
    <div class="card-header">Filters</div>
    <div class="card-body">
        <form class="form-row" method="GET">
            <div class="col-md-3 mb-2"><select name="status" class="form-control"><option value="">All Statuses</option>@foreach(['draft','scheduled','published','expired','archived','cancelled'] as $status)<option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
            <div class="col-md-3 mb-2"><select name="priority_level" class="form-control"><option value="">All Priorities</option>@foreach(['normal','important','urgent','critical'] as $priority)<option value="{{ $priority }}" @selected(($filters['priority_level'] ?? '') === $priority)>{{ ucfirst($priority) }}</option>@endforeach</select></div>
            <div class="col-md-3 mb-2"><label class="mb-0 mt-2"><input type="checkbox" name="urgent_only" value="1" @checked(($filters['urgent_only'] ?? null) == 1)> Urgent only</label></div>
            <div class="col-md-3 mb-2"><button class="btn btn-outline-primary btn-block">Apply</button></div>
        </form>
    </div>
</div>

@if(auth()->user()->hasPermission('create_announcement'))
<div class="card mb-3">
    <div class="card-header">Create Announcement</div>
    <div class="card-body">
        <form method="POST" action="{{ route('announcements.store') }}" class="form-row">@csrf
            <div class="col-md-4 mb-2"><input class="form-control" name="title" placeholder="Title" required></div>
            <div class="col-md-2 mb-2"><select class="form-control" name="announcement_type" required>@foreach(['company','branch','role','department','urgent_notice','policy_update','promotion_update','inventory_notice','maintenance_notice','system_notice'] as $type)<option value="{{ $type }}">{{ ucwords(str_replace('_',' ', $type)) }}</option>@endforeach</select></div>
            <div class="col-md-2 mb-2"><select class="form-control" name="priority_level" required>@foreach(['normal','important','urgent','critical'] as $priority)<option value="{{ $priority }}">{{ ucfirst($priority) }}</option>@endforeach</select></div>
            <div class="col-md-2 mb-2"><select class="form-control" name="status"><option value="draft">Draft</option><option value="published">Published</option><option value="scheduled">Scheduled</option></select></div>
            <div class="col-md-2 mb-2"><input class="form-control" type="datetime-local" name="publish_start_at"></div>
            <div class="col-md-12 mb-2"><textarea class="form-control" rows="3" name="content" placeholder="Announcement content" required></textarea></div>
            <div class="col-md-3 mb-2"><label><input type="checkbox" name="is_pinned" value="1"> Pinned</label></div>
            <div class="col-md-3 mb-2"><label><input type="checkbox" name="is_urgent" value="1"> Urgent</label></div>
            <div class="col-md-3 mb-2"><label><input type="checkbox" name="requires_acknowledgment" value="1"> Require acknowledgment</label></div>
            <div class="col-md-3 mb-2"><button class="btn btn-primary btn-block">Create</button></div>

            <div class="col-md-12 mt-3"><h6>Targets</h6></div>
            <div class="col-md-4 mb-2"><label class="small text-muted">All users</label><input type="hidden" name="targets[0][target_type]" value="all_users"></div>
            <div class="col-md-4 mb-2">
                <label class="small text-muted">Branch target</label>
                <select class="form-control" name="targets[1][target_id]">
                    <option value="">No branch target</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->branch_name }}</option>
                    @endforeach
                </select>
                <input type="hidden" name="targets[1][target_type]" value="branch">
            </div>
            <div class="col-md-4 mb-2">
                <label class="small text-muted">Role target</label>
                <select class="form-control" name="targets[2][target_id]">
                    <option value="">No role target</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                    @endforeach
                </select>
                <input type="hidden" name="targets[2][target_type]" value="role">
            </div>
        </form>
    </div>
</div>
@endif

<div class="card">
    <div class="card-body table-responsive p-0">
        <table class="table table-sm mb-0">
            <thead><tr><th>Announcement</th><th>Status</th><th>Priority</th><th>Published</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($announcements as $announcement)
                <tr class="{{ $announcement->is_urgent ? 'table-danger' : '' }}">
                    <td>
                        <a href="{{ route('announcements.show', $announcement) }}">{{ $announcement->title }}</a>
                        @if($announcement->is_pinned)<span class="badge badge-info ml-1">Pinned</span>@endif
                        @if($announcement->is_urgent)<span class="badge badge-danger ml-1">Urgent</span>@endif
                    </td>
                    <td><span class="badge badge-secondary">{{ ucfirst($announcement->status) }}</span></td>
                    <td>{{ ucfirst($announcement->priority_level) }}</td>
                    <td>{{ $announcement->published_at?->format('Y-m-d H:i') ?? '-' }}</td>
                    <td class="text-nowrap">
                        <form action="{{ route('announcements.read.mark', $announcement) }}" method="POST" class="d-inline">@csrf<button class="btn btn-xs btn-outline-primary">Read</button></form>
                        @if($announcement->requires_acknowledgment)
                        <form action="{{ route('announcements.acknowledge', $announcement) }}" method="POST" class="d-inline">@csrf<button class="btn btn-xs btn-outline-success">Acknowledge</button></form>
                        @endif
                        @if(auth()->user()->hasPermission('publish_announcement'))
                        <form action="{{ route('announcements.publish', $announcement) }}" method="POST" class="d-inline">@csrf<button class="btn btn-xs btn-success">Publish</button></form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted">No announcements available.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $announcements->links() }}</div>
</div>
@endsection
