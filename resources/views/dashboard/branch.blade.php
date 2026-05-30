@extends('layouts.app')

@section('page_title', 'Branch Dashboard')
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard.owner') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Branch</li>
@endsection

@section('content')
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="form-row align-items-end">
            <div class="col-md-3 col-sm-6 mb-2"><input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] ?? '' }}"></div>
            <div class="col-md-3 col-sm-6 mb-2"><input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] ?? '' }}"></div>
            <div class="col-md-4 col-sm-12 mb-2">
                <select name="branch_id" class="form-control">
                    @foreach($branches as $availableBranch)
                        <option value="{{ $availableBranch->id }}" @selected(($filters['branch_id'] ?? $branch->id) == $availableBranch->id)>{{ $availableBranch->branch_name ?? $availableBranch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 col-sm-12 mb-2"><button class="btn btn-primary btn-block touch-btn">Apply Filter</button></div>
        </form>
    </div>
</div>

@if($employeePanel)
<div class="card border-primary mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>Employee Self-Service</strong>
        <span class="text-muted small">Latest personal HR activity</span>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-12">
                <div class="card shadow-sm bg-light mb-0">
                    <div class="card-body py-3">
                        <div class="row align-items-center">
                            <div class="col-lg-4 mb-3 mb-lg-0">
                                <div class="small text-muted text-uppercase">Employee Profile</div>
                                <h5 class="mb-1">{{ $employeePanel['profile']['name'] }}</h5>
                                <div class="text-muted">{{ '@'.$employeePanel['profile']['username'] }}</div>
                                @if($employeePanel['profile']['email'])
                                    <div class="text-muted">{{ $employeePanel['profile']['email'] }}</div>
                                @endif
                            </div>
                            <div class="col-lg-5 mb-3 mb-lg-0">
                                <dl class="row mb-0 small">
                                    <dt class="col-sm-4">Role</dt>
                                    <dd class="col-sm-8">{{ $employeePanel['profile']['role'] }}</dd>
                                    <dt class="col-sm-4">Branch</dt>
                                    <dd class="col-sm-8">{{ $employeePanel['profile']['branch'] }}</dd>
                                    <dt class="col-sm-4">Status</dt>
                                    <dd class="col-sm-8 text-capitalize">{{ str_replace('_', ' ', $employeePanel['profile']['status']) }}</dd>
                                    <dt class="col-sm-4">Today Shift</dt>
                                    <dd class="col-sm-8">
                                        @if($employeePanel['profile']['today_schedule'])
                                            {{ $employeePanel['profile']['today_schedule']['window'] }}
                                            <span class="text-muted">({{ $employeePanel['profile']['today_schedule']['date'] }})</span>
                                            @if($employeePanel['profile']['today_shift_status'])
                                                <span class="badge badge-{{ $employeePanel['profile']['today_shift_status']['tone'] }} ml-1" title="Shift timing status">{{ $employeePanel['profile']['today_shift_status']['label'] }}</span>
                                            @endif
                                            @if($employeePanel['profile']['today_attendance_status'])
                                                <span class="badge badge-{{ $employeePanel['profile']['today_attendance_status']['tone'] }} ml-1" title="Attendance completion status">{{ $employeePanel['profile']['today_attendance_status']['label'] }}</span>
                                            @endif
                                        @else
                                            <span class="text-muted">No schedule set</span>
                                        @endif
                                    </dd>
                                    <dt class="col-sm-4">Next Shift</dt>
                                    <dd class="col-sm-8">
                                        @if($employeePanel['profile']['next_schedule'])
                                            {{ $employeePanel['profile']['next_schedule']['window'] }}
                                            <span class="text-muted">({{ $employeePanel['profile']['next_schedule']['date'] }})</span>
                                        @else
                                            <span class="text-muted">No upcoming shift</span>
                                        @endif
                                    </dd>
                                    <dt class="col-sm-4">Last Attendance Sync</dt>
                                    <dd class="col-sm-8">
                                        @if($employeePanel['profile']['last_sync'])
                                            {{ $employeePanel['profile']['last_sync']['at'] }}
                                            <span class="text-muted">(Source: {{ $employeePanel['profile']['last_sync']['source'] }}, {{ $employeePanel['profile']['last_sync']['relative'] }})</span>
                                        @else
                                            <span class="text-muted">Not synced yet</span>
                                        @endif
                                    </dd>
                                    <dt class="col-sm-4">Badge Guide</dt>
                                    <dd class="col-sm-8">
                                        <div class="d-flex flex-wrap align-items-center">
                                            <span class="badge badge-success mr-1 mb-1" title="Clock-in is on or before schedule">On Time</span>
                                            <span class="badge badge-danger mr-1 mb-1" title="Clock-in is after scheduled time">Late</span>
                                            <span class="badge badge-warning mr-1 mb-1" title="No time-in or no time-out yet">Pending</span>
                                            <span class="badge badge-primary mb-1" title="Time-in and time-out are both recorded">Complete</span>
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                            <div class="col-lg-3">
                                <div class="small text-muted text-uppercase mb-2">Quick Links</div>
                                <div class="d-flex flex-wrap">
                                    @foreach($employeePanel['profile']['quick_links'] as $quickLink)
                                        <a href="{{ $quickLink['url'] }}" class="btn btn-sm btn-outline-primary mr-2 mb-2">{{ $quickLink['label'] }}</a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-2">
            @foreach ($employeePanel['cards'] as $card)
                <div class="col-lg col-md-6 mb-3">
                    <a href="{{ $card['url'] }}" class="text-decoration-none text-reset">
                        <div class="small-box bg-light border h-100 mb-0">
                            <div class="inner">
                                <p class="mb-1 text-muted">{{ $card['label'] }}</p>
                                <h4>{{ $card['value'] }}</h4>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

        <div class="row">
            <div class="col-lg-6 mb-3 mb-lg-0">
                <div class="card h-100 shadow-sm">
                    <div class="card-header"><strong>Latest Attendance</strong></div>
                    <div class="card-body">
                        @if($employeePanel['latest_attendance'])
                            <dl class="row mb-0">
                                <dt class="col-sm-4">Date</dt>
                                <dd class="col-sm-8">{{ optional($employeePanel['latest_attendance']->attendance_date)->format('Y-m-d') }}</dd>
                                <dt class="col-sm-4">Status</dt>
                                <dd class="col-sm-8 text-capitalize">{{ str_replace('_', ' ', $employeePanel['latest_attendance']->attendance_status) }}</dd>
                                <dt class="col-sm-4">Time In</dt>
                                <dd class="col-sm-8">{{ optional($employeePanel['latest_attendance']->time_in)->format('Y-m-d H:i') ?: 'N/A' }}</dd>
                                <dt class="col-sm-4">Time Out</dt>
                                <dd class="col-sm-8">{{ optional($employeePanel['latest_attendance']->time_out)->format('Y-m-d H:i') ?: 'N/A' }}</dd>
                                <dt class="col-sm-4">Branch</dt>
                                <dd class="col-sm-8">{{ $employeePanel['latest_attendance']->branch?->branch_name ?? $employeePanel['latest_attendance']->branch?->name ?? 'N/A' }}</dd>
                            </dl>
                        @else
                            <p class="text-muted mb-0">No attendance record available yet.</p>
                        @endif
                    </div>
                    <div class="card-footer bg-white text-right">
                        <a href="{{ route('hr.attendance.index') }}" class="btn btn-sm btn-outline-primary">Open Attendance</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-header"><strong>Latest Payslip</strong></div>
                    <div class="card-body">
                        @if($employeePanel['latest_payslip'])
                            <dl class="row mb-0">
                                <dt class="col-sm-4">Payslip #</dt>
                                <dd class="col-sm-8">{{ $employeePanel['latest_payslip']->payslip_number }}</dd>
                                <dt class="col-sm-4">Period</dt>
                                <dd class="col-sm-8">{{ $employeePanel['latest_payslip']->payrollItem?->run?->period?->period_code ?? 'N/A' }}</dd>
                                <dt class="col-sm-4">Generated</dt>
                                <dd class="col-sm-8">{{ optional($employeePanel['latest_payslip']->generated_at)->format('Y-m-d H:i') ?: 'N/A' }}</dd>
                                <dt class="col-sm-4">Net Pay</dt>
                                <dd class="col-sm-8">{{ number_format((float) ($employeePanel['latest_payslip']->payrollItem?->net_pay ?? 0), 2) }}</dd>
                            </dl>
                        @else
                            <p class="text-muted mb-0">No payslip available yet.</p>
                        @endif
                    </div>
                    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                        <a href="{{ route('hr.payslips.index') }}" class="btn btn-sm btn-outline-primary">Open Payslips</a>
                        @if($employeePanel['latest_payslip'])
                            <a href="{{ route('hr.payslips.download', $employeePanel['latest_payslip']) }}" class="btn btn-sm btn-primary">Download Latest</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="{{ $employeePanel['can_access_chat'] ? 'col-xl-7 mb-3 mb-xl-0' : 'col-12' }}">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <strong>Latest Announcements</strong>
                        <a href="{{ route('announcements.index') }}" class="btn btn-sm btn-outline-primary">Open Announcements</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Priority</th>
                                        <th>Posted By</th>
                                        <th>Published</th>
                                        <th class="text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($employeePanel['recent_announcements'] as $announcement)
                                        @php $readStatus = $announcement->reads->first()?->acknowledgment_status ?? 'unread'; @endphp
                                        <tr class="{{ $announcement->is_urgent ? 'table-danger' : '' }}">
                                            <td>
                                                <a href="{{ route('announcements.show', $announcement) }}">{{ $announcement->title }}</a>
                                                @if($announcement->is_pinned)<span class="badge badge-info ml-1">Pinned</span>@endif
                                                @if($announcement->is_urgent)<span class="badge badge-danger ml-1">Urgent</span>@endif
                                            </td>
                                            <td>{{ ucfirst($announcement->priority_level) }}</td>
                                            <td>{{ $announcement->creator?->display_name ?? 'System' }}</td>
                                            <td>{{ optional($announcement->published_at)->format('Y-m-d H:i') ?: '-' }}</td>
                                            <td class="text-right text-nowrap">
                                                @if($readStatus === 'acknowledged')
                                                    <span class="badge badge-success">Acknowledged</span>
                                                @elseif($readStatus === 'read')
                                                    <span class="badge badge-info mr-1">Read</span>
                                                    @if($announcement->requires_acknowledgment)
                                                        <form action="{{ route('announcements.acknowledge', $announcement) }}" method="POST" class="d-inline">@csrf<button class="btn btn-xs btn-outline-success">Acknowledge</button></form>
                                                    @endif
                                                @else
                                                    <form action="{{ route('announcements.read.mark', $announcement) }}" method="POST" class="d-inline">@csrf<button class="btn btn-xs btn-outline-primary">Mark Read</button></form>
                                                    @if($announcement->requires_acknowledgment)
                                                        <form action="{{ route('announcements.acknowledge', $announcement) }}" method="POST" class="d-inline">@csrf<button class="btn btn-xs btn-outline-success">Acknowledge</button></form>
                                                    @endif
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center text-muted">No announcements available.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            @if($employeePanel['can_access_chat'])
                <div class="col-xl-5">
                    <div class="card shadow-sm h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <strong>Recent Chat Activity</strong>
                            <a href="{{ route('chat.index') }}" class="btn btn-sm btn-outline-primary">Open Chat</a>
                        </div>
                        <div class="card-body">
                            <div class="d-flex flex-wrap mb-3">
                                <span class="badge badge-light border mr-2 mb-2">Active Rooms: {{ $employeePanel['active_chat_rooms'] }}</span>
                                <span class="badge badge-light border mb-2">Unread Messages: {{ collect($employeePanel['cards'])->firstWhere('label', 'Unread Messages')['value'] ?? 0 }}</span>
                            </div>

                            @forelse($employeePanel['recent_messages'] as $message)
                                @php
                                    $isUnread = $message->sender_id !== auth()->id() && $message->reads->isEmpty();
                                @endphp
                                <div class="border rounded px-3 py-2 mb-2">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="font-weight-semibold">{{ $message->room?->room_name ?? 'Chat Room' }}</div>
                                            <div class="small text-muted">{{ $message->sender?->display_name ?? 'Unknown sender' }}</div>
                                        </div>
                                        <div class="text-right ml-2">
                                            @if($isUnread)
                                                <span class="badge badge-warning">Unread</span>
                                            @else
                                                <span class="badge badge-light border">Seen</span>
                                            @endif
                                            <div class="small text-muted mt-1">{{ optional($message->created_at)->format('Y-m-d H:i') ?: '-' }}</div>
                                        </div>
                                    </div>
                                    <p class="mb-2 mt-2 text-muted">{{ \Illuminate\Support\Str::limit($message->message_body, 120) }}</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <a href="{{ route('chat.rooms.show', $message->room) }}" class="btn btn-xs btn-outline-primary">Open Room</a>
                                        @if($isUnread)
                                            <form action="{{ route('chat.rooms.read.mark', $message->room) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button class="btn btn-xs btn-outline-success">Mark Room Read</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <p class="text-muted mb-0">No recent chat activity yet.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endif
        </div>

        @if($employeePanel['can_view_notifications'])
            <div class="row mt-3">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <strong>Recent Notifications</strong>
                            <a href="{{ route('communication.notifications.index') }}" class="btn btn-sm btn-outline-primary">Open Notification Center</a>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <span class="badge badge-light border">Unread Notifications: {{ $employeePanel['unread_notifications'] }}</span>
                            </div>

                            <div class="row">
                                @forelse($employeePanel['recent_notifications'] as $notification)
                                    <div class="col-lg-6 mb-3">
                                        <div class="border rounded h-100 px-3 py-3 {{ $notification->is_read ? 'text-muted' : '' }}">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div>
                                                    <div class="font-weight-semibold">{{ $notification->title }}</div>
                                                    <div class="small text-muted">{{ ucfirst(str_replace('_', ' ', $notification->category)) }}</div>
                                                </div>
                                                @if($notification->is_read)
                                                    <span class="badge badge-light border">Read</span>
                                                @else
                                                    <span class="badge badge-warning">Unread</span>
                                                @endif
                                            </div>

                                            <p class="mb-2">{{ $notification->message }}</p>
                                            <div class="small text-muted mb-3">{{ optional($notification->created_at)->format('Y-m-d H:i') ?: '-' }}</div>

                                            <div class="d-flex justify-content-between align-items-center">
                                                @if(($notification->payload['route'] ?? null))
                                                    <a href="{{ $notification->payload['route'] }}" class="btn btn-xs btn-outline-secondary">Open</a>
                                                @else
                                                    <span></span>
                                                @endif

                                                @if(! $notification->is_read)
                                                    <form method="POST" action="{{ route('communication.notifications.read', $notification) }}" class="d-inline">
                                                        @csrf
                                                        <button class="btn btn-xs btn-outline-success">Mark Read</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12">
                                        <p class="text-muted mb-0">No communication notifications yet.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endif

<div class="row">
    @foreach ($summary['cards'] as $metric)
        <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
            <div class="small-box bg-white border metric-card">
                <div class="inner">
                    <p class="mb-1 text-muted">{{ $metric['label'] }}</p>
                    <h4>{{ $metric['value'] }}</h4>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row">
    @foreach ($summary['charts'] as $chartKey => $points)
        <div class="col-lg-6 mb-3">
            <div class="card chart-card">
                <div class="card-header"><strong>{{ ucwords(str_replace('_', ' ', $chartKey)) }}</strong></div>
                <div class="card-body"><div style="height: 260px"><canvas id="{{ $chartKey }}"></canvas></div></div>
            </div>
        </div>
    @endforeach
</div>

<div class="row">
    <div class="col-lg-6 mb-3">
        <div class="card h-100">
            <div class="card-header"><strong>KPI Alerts</strong></div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($summary['kpi_alerts'] as $alert)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>{{ $alert['message'] }}</span>
                            <span class="badge badge-{{ $alert['severity'] === 'high' ? 'danger' : 'warning' }}">{{ strtoupper($alert['severity']) }}</span>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-muted">No active KPI alerts.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-3">
        <div class="card h-100">
            <div class="card-header"><strong>Recent Branch Sales</strong></div>
            <div class="card-body table-responsive p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Sale #</th><th>Cashier</th><th>Total</th></tr></thead>
                    <tbody>
                        @foreach($summary['tables']['recent_branch_sales'] as $sale)
                            <tr>
                                <td>{{ $sale->sales_number }}</td>
                                <td>{{ $sale->cashier?->display_name }}</td>
                                <td>{{ number_format((float) $sale->total_amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const chartPayloads = @json($summary['charts']);
Object.entries(chartPayloads).forEach(([chartKey, points]) => {
    const target = document.getElementById(chartKey);
    if (!target) return;

    const labels = points.map(point => point.label ?? 'N/A');
    const values = points.map(point => Number(point.value ?? 0));

    new Chart(target, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: chartKey.replaceAll('_', ' '),
                data: values,
                borderColor: 'rgba(40,167,69,1)',
                backgroundColor: 'rgba(40,167,69,0.2)',
                tension: 0.3,
                fill: true
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
});
</script>
@endpush
