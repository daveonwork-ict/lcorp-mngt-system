@extends('layouts.app')

@section('page_title', 'Airtime Transactions')
@section('content')
@php
    $todayTransactions = $transactions->where('processed_at', '>=', now()->startOfDay());
    $todaySales = $todayTransactions->sum('load_amount');
    $todayCount = $todayTransactions->count();
@endphp

<div class="row">
    <div class="col-xl-8">
        <div class="card shadow-sm mb-3">
            <div class="card-header border-0 pb-0">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <strong class="h5 mb-2 mb-md-0">Airtime Terminal</strong>
                    <div class="d-flex flex-wrap">
                        <span class="badge badge-pill badge-info mr-2 mb-1">Today Tx: {{ number_format($todayCount) }}</span>
                        <span class="badge badge-pill badge-success mb-1">Today Load: PHP {{ number_format($todaySales, 2) }}</span>
                    </div>
                </div>
            </div>
            <div class="card-body pt-3">
                <form method="POST" action="{{ route('airtime.transactions.store') }}" id="airtimeForm" class="form-row">
                    @csrf
                    <div class="col-md-4 mb-2">
                        <label class="small text-muted mb-1">Branch</label>
                        <select class="form-control form-control-lg" name="branch_id" id="branchSelect" required>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" @selected(old('branch_id', $filters['branch_id'] ?? null) == $branch->id)>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="small text-muted mb-1">Provider</label>
                        <select class="form-control form-control-lg" name="provider_id" id="providerSelect" required>
                            @foreach($providers as $provider)
                                <option value="{{ $provider->id }}" @selected(old('provider_id') == $provider->id)>{{ $provider->provider_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="small text-muted mb-1">Wallet</label>
                        <select class="form-control form-control-lg" name="wallet_id" id="walletSelect" required>
                            @foreach($wallets as $wallet)
                                <option value="{{ $wallet->id }}"
                                    data-branch="{{ $wallet->branch_id }}"
                                    data-provider="{{ $wallet->provider_id }}"
                                    @selected(old('wallet_id') == $wallet->id)>
                                    {{ $wallet->wallet_number }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Wallet list follows selected branch and provider.</small>
                    </div>

                    <div class="col-md-6 mb-2">
                        <label class="small text-muted mb-1">Customer Mobile</label>
                        <input class="form-control form-control-lg" name="customer_mobile_number" id="mobileInput" value="{{ old('customer_mobile_number') }}" placeholder="09xxxxxxxxx" required>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="small text-muted mb-1">Load Amount</label>
                        <div class="input-group input-group-lg">
                            <div class="input-group-prepend"><span class="input-group-text">PHP</span></div>
                            <input class="form-control" type="number" step="0.01" min="0.01" name="load_amount" id="amountInput" value="{{ old('load_amount') }}" required>
                        </div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="small text-muted mb-1">Status</label>
                        <select class="form-control form-control-lg" name="transaction_status">
                            <option value="successful" @selected(old('transaction_status', 'successful') === 'successful')>Successful</option>
                            <option value="pending" @selected(old('transaction_status') === 'pending')>Pending</option>
                            <option value="failed" @selected(old('transaction_status') === 'failed')>Failed</option>
                            <option value="cancelled" @selected(old('transaction_status') === 'cancelled')>Cancelled</option>
                        </select>
                    </div>

                    <div class="col-12 mb-3">
                        <label class="small text-muted mb-1 d-block">Quick Amount</label>
                        <div class="d-flex flex-wrap" id="quickAmounts">
                            <button type="button" class="btn btn-outline-primary btn-touch-amount mr-2 mb-2" data-amount="10">10</button>
                            <button type="button" class="btn btn-outline-primary btn-touch-amount mr-2 mb-2" data-amount="20">20</button>
                            <button type="button" class="btn btn-outline-primary btn-touch-amount mr-2 mb-2" data-amount="50">50</button>
                            <button type="button" class="btn btn-outline-primary btn-touch-amount mr-2 mb-2" data-amount="100">100</button>
                            <button type="button" class="btn btn-outline-primary btn-touch-amount mr-2 mb-2" data-amount="300">300</button>
                            <button type="button" class="btn btn-outline-primary btn-touch-amount mr-2 mb-2" data-amount="500">500</button>
                            <button type="button" class="btn btn-outline-primary btn-touch-amount mr-2 mb-2" data-amount="1000">1000</button>
                        </div>
                    </div>

                    <div class="col-md-4 mb-2">
                        <label class="small text-muted mb-1">Payment Method</label>
                        <select class="form-control form-control-lg" name="payment_method_id">
                            <option value="">None</option>
                            @foreach($paymentMethods as $method)
                                <option value="{{ $method->id }}" @selected(old('payment_method_id') == $method->id)>{{ $method->payment_method_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="small text-muted mb-1">Reference</label>
                        <input class="form-control form-control-lg" name="payment_reference" value="{{ old('payment_reference') }}" placeholder="Optional reference">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="small text-muted mb-1">Remarks</label>
                        <input class="form-control form-control-lg" name="remarks" value="{{ old('remarks') }}" placeholder="Optional notes">
                    </div>

                    <div class="col-md-6 mt-2">
                        <button class="btn btn-success btn-lg btn-block">Process Load Transaction</button>
                    </div>
                    <div class="col-md-3 mt-2">
                        <button class="btn btn-outline-secondary btn-lg btn-block" type="reset" id="resetAirtimeForm">Clear</button>
                    </div>
                    <div class="col-md-3 mt-2">
                        <a href="{{ route('airtime.transactions.index') }}" class="btn btn-light border btn-lg btn-block">Refresh</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Transaction Feed</strong>
                <span class="badge badge-light border">{{ $transactions->total() }} records</span>
            </div>
            <div class="card-body p-2 p-md-3">
                @forelse($transactions as $tx)
                    <div class="transaction-tile mb-2">
                        <div class="d-flex justify-content-between align-items-start flex-wrap mb-1">
                            <div>
                                <div class="font-weight-bold">{{ $tx->transaction_number }}</div>
                                <div class="small text-muted">{{ $tx->branch?->name }} | {{ $tx->provider?->provider_name }}</div>
                            </div>
                            <span class="badge badge-pill status-pill status-{{ $tx->transaction_status }}">{{ strtoupper($tx->transaction_status) }}</span>
                        </div>
                        <div class="d-flex justify-content-between flex-wrap small">
                            <span class="mr-3"><strong>Mobile:</strong> {{ $tx->customer_mobile_number }}</span>
                            <span class="mr-3"><strong>Load:</strong> PHP {{ number_format($tx->load_amount, 2) }}</span>
                            <span class="mr-3"><strong>Commission:</strong> PHP {{ number_format($tx->commission_amount, 2) }}</span>
                            <span><strong>Processed:</strong> {{ $tx->processed_at }}</span>
                        </div>
                        <div class="mt-2 text-right">
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('airtime.transactions.show', $tx) }}">View Details</a>
                        </div>
                    </div>
                @empty
                    <div class="alert alert-light border mb-0">No airtime transactions found for the current filters.</div>
                @endforelse
            </div>
            <div class="card-footer">{{ $transactions->links() }}</div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card shadow-sm mb-3 sticky-filter">
            <div class="card-header"><strong>Filter Transactions</strong></div>
            <div class="card-body">
                <form method="GET" class="form-row">
                    <div class="col-6 mb-2">
                        <label class="small text-muted mb-1">From</label>
                        <input class="form-control" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}">
                    </div>
                    <div class="col-6 mb-2">
                        <label class="small text-muted mb-1">To</label>
                        <input class="form-control" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}">
                    </div>
                    <div class="col-12 mb-2">
                        <label class="small text-muted mb-1">Branch</label>
                        <select class="form-control" name="branch_id">
                            <option value="">All Branches</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" @selected(($filters['branch_id'] ?? null) == $branch->id)>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 mb-2">
                        <label class="small text-muted mb-1">Provider</label>
                        <select class="form-control" name="provider_id">
                            <option value="">All Providers</option>
                            @foreach($providers as $provider)
                                <option value="{{ $provider->id }}" @selected(($filters['provider_id'] ?? null) == $provider->id)>{{ $provider->provider_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="small text-muted mb-1">Status</label>
                        <select class="form-control" name="status">
                            <option value="">All Status</option>
                            <option value="successful" @selected(($filters['status'] ?? null) === 'successful')>Successful</option>
                            <option value="pending" @selected(($filters['status'] ?? null) === 'pending')>Pending</option>
                            <option value="failed" @selected(($filters['status'] ?? null) === 'failed')>Failed</option>
                            <option value="cancelled" @selected(($filters['status'] ?? null) === 'cancelled')>Cancelled</option>
                            <option value="reversed" @selected(($filters['status'] ?? null) === 'reversed')>Reversed</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <button class="btn btn-outline-primary btn-block">Apply</button>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('airtime.transactions.index') }}" class="btn btn-light border btn-block">Reset</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.btn-touch-amount {
    min-width: 86px;
    min-height: 48px;
    font-weight: 600;
}

.transaction-tile {
    border: 1px solid #dbe3ec;
    border-radius: 12px;
    padding: 0.85rem;
    background: #ffffff;
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

.sticky-filter {
    position: sticky;
    top: 1rem;
}

@media (max-width: 1199.98px) {
    .sticky-filter {
        position: static;
    }
}
</style>
@endpush

@push('scripts')
<script>
(function () {
    const branchSelect = document.getElementById('branchSelect');
    const providerSelect = document.getElementById('providerSelect');
    const walletSelect = document.getElementById('walletSelect');
    const amountInput = document.getElementById('amountInput');
    const mobileInput = document.getElementById('mobileInput');
    const resetButton = document.getElementById('resetAirtimeForm');

    function filterWalletOptions() {
        if (!walletSelect || !branchSelect || !providerSelect) {
            return;
        }

        const branchId = String(branchSelect.value || '');
        const providerId = String(providerSelect.value || '');
        let firstVisible = null;
        let activeVisible = false;

        Array.from(walletSelect.options).forEach(function (option) {
            const optionBranch = option.dataset.branch;
            const optionProvider = option.dataset.provider;
            const visible = (!branchId || optionBranch === branchId) && (!providerId || optionProvider === providerId);

            option.hidden = !visible;
            option.disabled = !visible;

            if (visible && !firstVisible) {
                firstVisible = option;
            }

            if (visible && option.selected) {
                activeVisible = true;
            }
        });

        if (!activeVisible && firstVisible) {
            walletSelect.value = firstVisible.value;
        }
    }

    document.querySelectorAll('.btn-touch-amount').forEach(function (button) {
        button.addEventListener('click', function () {
            amountInput.value = button.dataset.amount;
            amountInput.focus();
        });
    });

    if (branchSelect) {
        branchSelect.addEventListener('change', filterWalletOptions);
    }

    if (providerSelect) {
        providerSelect.addEventListener('change', filterWalletOptions);
    }

    if (resetButton) {
        resetButton.addEventListener('click', function () {
            setTimeout(function () {
                filterWalletOptions();
                if (mobileInput) {
                    mobileInput.focus();
                }
            }, 0);
        });
    }

    filterWalletOptions();
})();
</script>
@endpush
