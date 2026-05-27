@extends('layouts.app')

@section('page_title', 'Data Imports')
@section('content')
<div class="card mb-3">
    <div class="card-body">
        <form method="POST" action="{{ route('deployment.imports.store') }}" enctype="multipart/form-data" class="form-row align-items-end">@csrf
            <div class="col-md-3 col-sm-6 mb-2">
                <select name="module_name" class="form-control" required>
                    <option value="">Select module</option>
                    @foreach($modules as $key => $module)
                        <option value="{{ $key }}">{{ $module['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-5 col-sm-6 mb-2"><input type="file" name="file" class="form-control-file" accept=".csv" required></div>
            <div class="col-md-2 col-sm-6 mb-2"><button class="btn btn-primary btn-block touch-btn">Preview Import</button></div>
            <div class="col-md-2 col-sm-6 mb-2 text-md-right"><small class="text-muted">CSV only. Use template before upload.</small></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive p-0">
        <table class="table table-sm mb-0">
            <thead><tr><th>Import #</th><th>Module</th><th>Status</th><th>Total</th><th>Success</th><th>Failed</th><th>Imported By</th><th></th></tr></thead>
            <tbody>
            @forelse($logs as $log)
                <tr>
                    <td>{{ $log->import_number }}</td>
                    <td>{{ strtoupper($log->module_name) }}</td>
                    <td><span class="badge badge-{{ $log->status === 'completed' ? 'success' : ($log->failed_rows > 0 ? 'warning' : 'secondary') }}">{{ strtoupper($log->status) }}</span></td>
                    <td>{{ $log->total_rows }}</td>
                    <td>{{ $log->successful_rows }}</td>
                    <td>{{ $log->failed_rows }}</td>
                    <td>{{ $log->importer?->display_name ?? '-' }}</td>
                    <td><a class="btn btn-sm btn-outline-primary touch-btn" href="{{ route('deployment.imports.show', $log) }}">Open</a></td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted">No import logs yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center flex-wrap">
        {{ $logs->links() }}
        <div class="text-muted mt-2 mt-md-0">Templates: @foreach($modules as $key => $module)<a href="{{ route('deployment.imports.template', $key) }}">{{ $module['label'] }}</a>@if(! $loop->last) | @endif @endforeach</div>
    </div>
</div>
@endsection
