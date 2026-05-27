@extends('layouts.app')

@section('page_title', 'Staff Accountabilities')
@section('content')
<div class="card"><div class="card-header">Issued Supplies by Employee</div><div class="table-responsive p-0"><table class="table table-sm mb-0"><thead><tr><th>Date</th><th>Employee</th><th>Branch</th><th>Supply</th><th>Qty</th><th>Purpose</th></tr></thead><tbody>@forelse($records as $row)<tr><td>{{ $row->date_issued }}</td><td>{{ $row->employee?->full_name ?? $row->employee?->name }}</td><td>{{ $row->branch?->branch_name ?? $row->branch?->name }}</td><td>{{ $row->supply?->supply_name }}</td><td>{{ $row->quantity_issued }}</td><td>{{ $row->purpose }}</td></tr>@empty<tr><td colspan="6" class="text-center text-muted">No accountability records.</td></tr>@endforelse</tbody></table></div><div class="card-footer">{{ $records->links() }}</div></div>
@endsection
