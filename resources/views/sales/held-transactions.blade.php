@extends('layouts.app')

@section('page_title', 'Held Transactions')
@section('content')
<div class="card">
    <div class="card-body table-responsive p-0">
        <table class="table table-sm mb-0">
            <thead><tr><th>Hold #</th><th>Branch</th><th>Cashier</th><th>Total</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @foreach($heldTransactions as $held)
                    <tr>
                        <td>{{ $held->hold_number }}</td>
                        <td>{{ $held->branch?->name }}</td>
                        <td>{{ $held->cashier?->display_name }}</td>
                        <td>{{ number_format($held->total_amount, 2) }}</td>
                        <td>{{ ucfirst($held->status) }}</td>
                        <td>
                            @if($held->status === 'held')
                                <form method="POST" action="{{ route('sales.held.resume', $held) }}" class="d-inline">@csrf<button class="btn btn-xs btn-primary">Resume</button></form>
                                <form method="POST" action="{{ route('sales.held.cancel', $held) }}" class="d-inline">@csrf<button class="btn btn-xs btn-outline-danger">Cancel</button></form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $heldTransactions->links() }}</div>
</div>
@endsection
