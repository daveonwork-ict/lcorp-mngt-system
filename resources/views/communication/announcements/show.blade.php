@extends('layouts.app')

@section('page_title', 'Announcement Details')
@section('content')
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>{{ $announcement->title }}</strong>
        <div>
            <span class="badge badge-{{ in_array($announcement->priority_level, ['urgent','critical']) ? 'danger' : 'secondary' }}">{{ ucfirst($announcement->priority_level) }}</span>
            <span class="badge badge-info">{{ ucfirst($announcement->status) }}</span>
        </div>
    </div>
    <div class="card-body">
        <p class="mb-3">{{ $announcement->content }}</p>
        <div class="small text-muted">Type: {{ ucwords(str_replace('_', ' ', $announcement->announcement_type)) }}</div>
        <div class="small text-muted">Published: {{ $announcement->published_at?->format('Y-m-d H:i') ?? '-' }}</div>
        <div class="small text-muted">Expires: {{ $announcement->publish_end_at?->format('Y-m-d H:i') ?? '-' }}</div>
    </div>
</div>

@if(auth()->user()->hasPermission('edit_announcement'))
<div class="card mb-3">
    <div class="card-header">Edit Announcement</div>
    <div class="card-body">
        <form method="POST" action="{{ route('announcements.update', $announcement) }}" class="form-row">@csrf @method('PUT')
            <div class="col-md-4 mb-2"><input class="form-control" name="title" value="{{ $announcement->title }}" required></div>
            <div class="col-md-2 mb-2"><input class="form-control" name="announcement_type" value="{{ $announcement->announcement_type }}" required></div>
            <div class="col-md-2 mb-2"><select class="form-control" name="priority_level">@foreach(['normal','important','urgent','critical'] as $priority)<option value="{{ $priority }}" @selected($announcement->priority_level === $priority)>{{ ucfirst($priority) }}</option>@endforeach</select></div>
            <div class="col-md-2 mb-2"><select class="form-control" name="status">@foreach(['draft','scheduled','published','expired','archived','cancelled'] as $status)<option value="{{ $status }}" @selected($announcement->status === $status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
            <div class="col-md-2 mb-2"><button class="btn btn-primary btn-block">Update</button></div>
            <div class="col-md-12 mb-2"><textarea class="form-control" rows="3" name="content" required>{{ $announcement->content }}</textarea></div>
            <div class="col-md-3 mb-2"><label><input type="checkbox" name="is_pinned" value="1" @checked($announcement->is_pinned)> Pinned</label></div>
            <div class="col-md-3 mb-2"><label><input type="checkbox" name="is_urgent" value="1" @checked($announcement->is_urgent)> Urgent</label></div>
            <div class="col-md-3 mb-2"><label><input type="checkbox" name="requires_acknowledgment" value="1" @checked($announcement->requires_acknowledgment)> Requires acknowledgment</label></div>
            <div class="col-md-3 mb-2"><input class="form-control" type="datetime-local" name="publish_start_at" value="{{ $announcement->publish_start_at?->format('Y-m-d\\TH:i') }}"></div>
            <div class="col-md-3 mb-2"><input class="form-control" type="datetime-local" name="publish_end_at" value="{{ $announcement->publish_end_at?->format('Y-m-d\\TH:i') }}"></div>
            <input type="hidden" name="target_scope" value="{{ $announcement->target_scope }}">
        </form>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">Upload Attachment</div>
    <div class="card-body">
        <form action="{{ route('announcements.attachments.store', $announcement) }}" method="POST" enctype="multipart/form-data" class="form-inline">@csrf
            <input type="file" name="attachment" class="form-control mr-2" required>
            <button class="btn btn-outline-primary">Upload</button>
        </form>
    </div>
</div>
@endif

<div class="card mb-3">
    <div class="card-header">Attachments</div>
    <div class="card-body table-responsive p-0">
        <table class="table table-sm mb-0">
            <thead><tr><th>Name</th><th>Type</th><th>Size</th><th></th></tr></thead>
            <tbody>
            @forelse($announcement->attachments as $attachment)
                <tr>
                    <td>{{ $attachment->file_name }}</td>
                    <td>{{ $attachment->file_type }}</td>
                    <td>{{ number_format(($attachment->file_size ?? 0) / 1024, 2) }} KB</td>
                    <td><a class="btn btn-xs btn-outline-secondary" href="{{ route('announcements.attachments.download', $attachment) }}">Download</a></td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted">No attachments.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@if(auth()->user()->hasPermission('view_announcement_reads'))
<a href="{{ route('announcements.reads.index', $announcement) }}" class="btn btn-outline-dark">View Read Tracking</a>
@endif
@endsection
