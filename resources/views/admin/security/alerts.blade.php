@extends('layouts.app')

@section('page_title', 'Security Alerts')
@section('content')
<div class="card"><div class="table-responsive p-0"><table class="table table-sm mb-0"><thead><tr><th>Time</th><th>Type</th><th>Severity</th><th>Message</th><th>Status</th><th></th></tr></thead><tbody>@forelse($alerts as $alert)<tr><td>{{ $alert->alerted_at?->format('Y-m-d H:i:s') }}</td><td>{{ $alert->alert_type }}</td><td><span class="badge badge-{{ in_array($alert->severity, ['high','critical']) ? 'danger' : 'warning' }}">{{ strtoupper($alert->severity) }}</span></td><td>{{ $alert->message }}</td><td>{{ $alert->is_resolved ? 'Resolved' : 'Open' }}</td><td>@if(! $alert->is_resolved)<form method="POST" action="{{ route('admin.security.alerts.resolve', $alert) }}">@csrf<button class="btn btn-sm btn-outline-success">Resolve</button></form>@endif</td></tr>@empty<tr><td colspan="6" class="text-center text-muted">No security alerts.</td></tr>@endforelse</tbody></table></div><div class="card-footer">{{ $alerts->links() }}</div></div>
@endsection
