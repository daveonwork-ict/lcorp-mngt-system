@extends('layouts.app')

@section('page_title', 'Deployment Checklist')
@section('content')
<div class="card">
    <div class="table-responsive p-0">
        <table class="table table-sm mb-0">
            <thead><tr><th>Item</th><th>Status</th><th>Remarks</th><th>Checked By</th><th>Checked At</th><th></th></tr></thead>
            <tbody>
            @foreach($items as $item)
                <tr>
                    <td>{{ $item->item_label }}</td>
                    <td><span class="badge badge-{{ $item->status === 'passed' ? 'success' : ($item->status === 'failed' ? 'danger' : 'secondary') }}">{{ strtoupper($item->status) }}</span></td>
                    <td>{{ $item->remarks ?? '-' }}</td>
                    <td>{{ $item->checker?->display_name ?? '-' }}</td>
                    <td>{{ $item->checked_at ?? '-' }}</td>
                    <td>
                        <form method="POST" action="{{ route('deployment.checklists.update', $item) }}" class="d-flex flex-wrap gap-2">@csrf @method('PUT')
                            <select name="status" class="form-control form-control-sm mr-2" style="max-width: 150px">
                                <option value="pending" @selected($item->status === 'pending')>Pending</option>
                                <option value="passed" @selected($item->status === 'passed')>Passed</option>
                                <option value="failed" @selected($item->status === 'failed')>Failed</option>
                            </select>
                            <input name="remarks" class="form-control form-control-sm mr-2" placeholder="Remarks" value="{{ $item->remarks }}" style="max-width: 180px">
                            <button class="btn btn-sm btn-primary touch-btn">Update</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
