@extends('layouts.app')

@section('page_title', 'Financial Ledger')
@section('content')
<div class="card"><div class="card-body table-responsive p-0"><table class="table table-sm mb-0"><thead><tr><th>Date</th><th>Branch</th><th>Type</th><th>Ref</th><th>In</th><th>Out</th><th>Balance</th></tr></thead><tbody>@foreach($ledgers as $ledger)<tr><td>{{ $ledger->created_at }}</td><td>{{ $ledger->branch?->name }}</td><td>{{ $ledger->ledger_type }}</td><td>{{ $ledger->reference_type }} #{{ $ledger->reference_id }}</td><td>{{ number_format($ledger->amount_in,2) }}</td><td>{{ number_format($ledger->amount_out,2) }}</td><td>{{ number_format($ledger->running_balance,2) }}</td></tr>@endforeach</tbody></table></div><div class="card-footer">{{ $ledgers->links() }}</div></div>
@endsection
