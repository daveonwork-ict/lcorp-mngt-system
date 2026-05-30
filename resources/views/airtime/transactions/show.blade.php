@extends('layouts.app')

@section('page_title', 'Airtime Transaction Details')
@section('content')
<div class="row">
    <div class="col-xl-8">
        <div class="card shadow-sm mb-3">
            <div class="card-header border-0 pb-0">
                <div class="d-flex justify-content-between align-items-start flex-wrap">
                    <div>
                        <div class="small text-muted">Transaction Number</div>
                        <div class="h5 mb-0">{{ $transaction->transaction_number }}</div>
                    </div>
                    <span class="badge badge-pill status-pill status-{{ $transaction->transaction_status }} mt-1">
                        {{ strtoupper($transaction->transaction_status) }}
                    </span>
                </div>
            </div>
            <div class="card-body pt-3">
                <div class="row">
                    <div class="col-md-6 col-lg-3 mb-2">
                        <div class="detail-tile">
                            <div class="label">Load Amount</div>
                            <div class="value text-success">PHP {{ number_format($transaction->load_amount, 2) }}</div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 mb-2">
                        <div class="detail-tile">
                            <div class="label">Commission</div>
                            <div class="value">PHP {{ number_format($transaction->commission_amount, 2) }}</div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 mb-2">
                        <div class="detail-tile">
                            <div class="label">Customer Mobile</div>
                            <div class="value">{{ $transaction->customer_mobile_number }}</div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 mb-2">
                        <div class="detail-tile">
                            <div class="label">Processed At</div>
                            <div class="value">{{ $transaction->processed_at }}</div>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-6 mb-2"><strong>Branch:</strong> {{ $transaction->branch?->name }}</div>
                    <div class="col-md-6 mb-2"><strong>Provider:</strong> {{ $transaction->provider?->provider_name }}</div>
                    <div class="col-md-6 mb-2"><strong>Wallet:</strong> {{ $transaction->wallet?->wallet_number }}</div>
                    <div class="col-md-6 mb-2"><strong>Cashier:</strong> {{ $transaction->cashier?->display_name }}</div>
                    <div class="col-md-6 mb-2"><strong>Payment Method:</strong> {{ $transaction->paymentMethod?->payment_method_name ?: 'N/A' }}</div>
                    <div class="col-md-6 mb-2"><strong>Payment Reference:</strong> {{ $transaction->payment_reference ?: 'N/A' }}</div>
                    <div class="col-12 mb-2"><strong>Remarks:</strong> {{ $transaction->remarks ?: 'N/A' }}</div>
                    <div class="col-12 mb-0"><strong>Reversal Reason:</strong> {{ $transaction->reversal_reason ?: 'N/A' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card shadow-sm mb-3 sticky-panel">
            <div class="card-header"><strong>Actions</strong></div>
            <div class="card-body">
                <a href="{{ route('airtime.transactions.index') }}" class="btn btn-outline-secondary btn-lg btn-block mb-2">Back To Transactions</a>

                @if(in_array($transaction->transaction_status, ['successful', 'pending'], true))
                    <form method="POST" action="{{ route('airtime.transactions.reverse', $transaction) }}">
                        @csrf
                        <label class="small text-muted mb-1">Reversal Reason</label>
                        <textarea class="form-control mb-2" name="reversal_reason" rows="3" placeholder="Enter reason for reversal" required></textarea>
                        <button class="btn btn-danger btn-lg btn-block">Reverse Transaction</button>
                    </form>
                @else
                    <div class="alert alert-light border mb-0">
                        Reversal is available only for successful or pending transactions.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.detail-tile {
    border: 1px solid #dbe3ec;
    border-radius: 12px;
    padding: 0.75rem;
    background: #ffffff;
    min-height: 88px;
}

.detail-tile .label {
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #6c757d;
    margin-bottom: 0.35rem;
}

.detail-tile .value {
    font-weight: 600;
}

.status-pill {
    font-size: 0.72rem;
    letter-spacing: 0.04em;
}

.status-successful {
    background: #d4edda;
    color: #175c2c;
}

.status-pending {
    background: #fff3cd;
    color: #7c5700;
}

.status-failed,
.status-cancelled {
    background: #f8d7da;
    color: #7c1d25;
}

.status-reversed {
    background: #e2e3e5;
    color: #383d41;
}

.sticky-panel {
    position: sticky;
    top: 1rem;
}

@media (max-width: 1199.98px) {
    .sticky-panel {
        position: static;
    }
}
</style>
@endpush
