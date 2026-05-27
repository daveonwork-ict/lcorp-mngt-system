@extends('layouts.app')

@section('page_title', 'System Acceptance')
@section('content')
<div class="card mb-3">
    <div class="card-body">
        <form method="POST" action="{{ route('deployment.acceptance.store') }}" class="form-row align-items-end">@csrf
            <div class="col-md-3 col-sm-6 mb-2"><input name="acceptance_date" type="date" class="form-control"></div>
            <div class="col-md-3 col-sm-6 mb-2"><input name="accepted_by" class="form-control" placeholder="Accepted by user ID"></div>
            <div class="col-md-4 col-sm-12 mb-2"><input name="remarks" class="form-control" placeholder="Remarks"></div>
            <div class="col-md-2 col-sm-6 mb-2"><button class="btn btn-primary btn-block touch-btn">Save Acceptance</button></div>
        </form>
    </div>
</div>
<div class="card">
    <div class="table-responsive p-0">
        <table class="table table-sm mb-0">
            <thead><tr><th>Acceptance #</th><th>Branch</th><th>Status</th><th>Date</th><th>Prepared By</th><th>Accepted By</th><th>Remarks</th></tr></thead>
            <tbody>
            @forelse($records as $record)
                <tr>
                    <td>{{ $record->acceptance_number }}</td>
                    <td>{{ $record->branch?->branch_name ?? '-' }}</td>
                    <td><span class="badge badge-{{ $record->status === 'accepted' ? 'success' : ($record->status === 'ready_for_acceptance' ? 'warning' : 'secondary') }}">{{ strtoupper($record->status) }}</span></td>
                    <td>{{ $record->acceptance_date?->format('Y-m-d') ?? '-' }}</td>
                    <td>{{ $record->preparer?->display_name ?? '-' }}</td>
                    <td>{{ $record->acceptor?->display_name ?? '-' }}</td>
                    <td>{{ $record->remarks ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted">No acceptance records yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $records->links() }}</div>
</div>
@endsection
