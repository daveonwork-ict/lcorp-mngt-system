@extends('layouts.app')

@section('page_title', 'Cash Variances')
@section('content')
<div class="card"><div class="card-body table-responsive p-0"><table class="table table-sm mb-0"><thead><tr><th>Closing</th><th>Branch</th><th>Expected</th><th>Actual</th><th>Variance</th><th>Resolution</th><th></th></tr></thead><tbody>
@foreach($variances as $variance)
<tr>
<td>{{ $variance->dailyClosing?->closing_number }}</td>
<td>{{ $variance->branch?->name }}</td>
<td>{{ number_format($variance->expected_cash,2) }}</td>
<td>{{ number_format($variance->actual_cash,2) }}</td>
<td>{{ ucfirst($variance->variance_type) }} ({{ number_format($variance->variance_amount,2) }})</td>
<td>{{ ucfirst(str_replace('_',' ',$variance->resolution_status)) }}</td>
<td><form method="POST" action="{{ route('finance.variances.resolve', $variance) }}" class="form-inline">@csrf<select class="form-control form-control-sm mr-1" name="resolution_status"><option value="pending">Pending</option><option value="under_review">Under Review</option><option value="resolved">Resolved</option><option value="unresolved">Unresolved</option></select><button class="btn btn-xs btn-primary">Update</button></form></td>
</tr>
@endforeach
</tbody></table></div><div class="card-footer">{{ $variances->links() }}</div></div>
@endsection
