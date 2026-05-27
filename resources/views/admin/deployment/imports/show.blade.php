@extends('layouts.app')

@section('page_title', 'Import Preview')
@section('content')
<div class="card mb-3">
    <div class="card-body">
        <div class="row">
            <div class="col-md-3"><strong>Import #</strong><br>{{ $log->import_number }}</div>
            <div class="col-md-3"><strong>Module</strong><br>{{ strtoupper($log->module_name) }}</div>
            <div class="col-md-3"><strong>Status</strong><br>{{ strtoupper($log->status) }}</div>
            <div class="col-md-3"><strong>Rejected Rows</strong><br>{{ $log->failed_rows }}</div>
        </div>
        <div class="mt-3 d-flex flex-wrap gap-2">
            <form method="POST" action="{{ route('deployment.imports.confirm', $log) }}">@csrf<button class="btn btn-success touch-btn">Confirm Import</button></form>
            @if($log->rejected_rows_path)
                <a class="btn btn-outline-secondary touch-btn" href="{{ storage_path('app/'.$log->rejected_rows_path) }}" target="_blank">Rejected Rows Path</a>
            @endif
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">Preview Summary</div>
    <div class="card-body table-responsive">
        <table class="table table-sm mb-0">
            <thead><tr><th>Row</th><th>Errors</th><th>Payload</th></tr></thead>
            <tbody>
            @forelse($log->errors as $error)
                <tr>
                    <td>{{ $error->row_number }}</td>
                    <td>{{ implode(' | ', $error->error_messages ?? []) }}</td>
                    <td><code class="small">{{ json_encode($error->row_payload) }}</code></td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-center text-muted">No validation errors found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
