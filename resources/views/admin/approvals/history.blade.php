@extends('layouts.app')

@section('page_title', 'Approval History Timeline')
@section('content')
<div class="card"><div class="card-header">{{ $approval->approval_number }} Timeline</div><div class="card-body p-0"><div class="list-group list-group-flush">@forelse($approval->logs as $log)<div class="list-group-item"><span class="badge badge-secondary mr-2">{{ strtoupper(str_replace('_', ' ', $log->action)) }}</span>{{ $log->remarks ?: '-' }}<div class="small text-muted mt-1">By {{ $log->performer?->display_name ?? 'System' }} at {{ $log->performed_at?->format('Y-m-d H:i:s') }}</div></div>@empty<div class="list-group-item text-muted">No approval history available.</div>@endforelse</div></div></div>
@endsection
