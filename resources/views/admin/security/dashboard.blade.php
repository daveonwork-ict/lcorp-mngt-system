@extends('layouts.app')

@section('page_title', 'Security Dashboard')
@section('content')
<div class="row mb-3">
    @foreach($cards as $label => $value)
        <div class="col-xl-3 col-md-4 col-sm-6 mb-3">
            <div class="card h-100"><div class="card-body"><div class="small text-uppercase text-muted">{{ str_replace('_', ' ', $label) }}</div><div class="h4 mb-0">{{ $value }}</div></div></div>
        </div>
    @endforeach
</div>

<div class="row">
    <div class="col-lg-6 mb-3"><div class="card"><div class="card-header">Recent Failed Logins</div><div class="table-responsive p-0"><table class="table table-sm mb-0"><thead><tr><th>Time</th><th>User</th><th>IP</th></tr></thead><tbody>@forelse($failedLogins as $row)<tr><td>{{ $row->logged_at }}</td><td>{{ $row->user?->display_name ?? $row->login_identifier }}</td><td>{{ $row->ip_address }}</td></tr>@empty<tr><td colspan="3" class="text-center text-muted">No failed logins.</td></tr>@endforelse</tbody></table></div></div></div>
    <div class="col-lg-6 mb-3"><div class="card"><div class="card-header">Recent Audit Activity</div><div class="table-responsive p-0"><table class="table table-sm mb-0"><thead><tr><th>Time</th><th>Module</th><th>Action</th></tr></thead><tbody>@forelse($recentAudit as $row)<tr><td>{{ $row->created_at }}</td><td>{{ $row->module_name }}</td><td>{{ $row->action_type }}</td></tr>@empty<tr><td colspan="3" class="text-center text-muted">No audit activity.</td></tr>@endforelse</tbody></table></div></div></div>
</div>
@endsection
