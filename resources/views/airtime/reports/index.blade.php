@extends('layouts.app')

@section('page_title', 'Airtime Reports')
@section('content')
@php
    $txnTotal = $transactions->getCollection()->sum('load_amount');
    $commissionTotal = $transactions->getCollection()->sum('commission_amount');
    $walletTotal = $walletBalances->getCollection()->sum('current_balance');
    $fundingTotal = $fundings->getCollection()->sum('amount');
@endphp

<div class="row">
    <div class="col-xl-9">
        <div class="card shadow-sm mb-3">
            <div class="card-header border-0 pb-0">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <strong class="h5 mb-2 mb-md-0">Airtime Analytics Console</strong>
                    <div>
                        <a class="btn btn-outline-success btn-sm mr-2" href="{{ route('airtime.reports.export-csv', request()->query()) }}">Export CSV</a>
                        <a class="btn btn-outline-secondary btn-sm" href="{{ route('airtime.reports.print', request()->query()) }}" target="_blank">Print View</a>
                    </div>
                </div>
            </div>
            <div class="card-body pt-3">
                <div class="row">
                    <div class="col-md-6 col-lg-3 mb-2">
                        <div class="kpi-tile">
                            <div class="kpi-label">Visible Transactions</div>
                            <div class="kpi-value">{{ number_format($transactions->total()) }}</div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 mb-2">
                        <div class="kpi-tile">
                            <div class="kpi-label">Visible Load</div>
                            <div class="kpi-value text-success">PHP {{ number_format($txnTotal, 2) }}</div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 mb-2">
                        <div class="kpi-tile">
                            <div class="kpi-label">Visible Commission</div>
                            <div class="kpi-value">PHP {{ number_format($commissionTotal, 2) }}</div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 mb-2">
                        <div class="kpi-tile">
                            <div class="kpi-label">Visible Wallet Balance</div>
                            <div class="kpi-value">PHP {{ number_format($walletTotal, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Load Transactions</strong>
                <span class="badge badge-light border">{{ $transactions->total() }} entries</span>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Branch</th>
                            <th>Provider</th>
                            <th>Mobile</th>
                            <th>Load</th>
                            <th>Commission</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $tx)
                            <tr>
                                <td>{{ $tx->transaction_number }}</td>
                                <td>{{ $tx->branch?->name }}</td>
                                <td>{{ $tx->provider?->provider_name }}</td>
                                <td>{{ $tx->customer_mobile_number }}</td>
                                <td>PHP {{ number_format($tx->load_amount, 2) }}</td>
                                <td>PHP {{ number_format($tx->commission_amount, 2) }}</td>
                                <td><span class="badge badge-pill status-{{ $tx->transaction_status }}">{{ strtoupper($tx->transaction_status) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-3">No transaction data for this filter set.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">{{ $transactions->links() }}</div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Wallet Balance Report</strong>
                <span class="badge badge-light border">{{ $walletBalances->total() }} wallets</span>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Wallet</th>
                            <th>Branch</th>
                            <th>Provider</th>
                            <th>Current Balance</th>
                            <th>Low Threshold</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($walletBalances as $wallet)
                            <tr>
                                <td>{{ $wallet->wallet_number }}</td>
                                <td>{{ $wallet->branch?->name }}</td>
                                <td>{{ $wallet->provider?->provider_name }}</td>
                                <td>PHP {{ number_format($wallet->current_balance, 2) }}</td>
                                <td>PHP {{ number_format($wallet->low_balance_threshold, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">No wallet data for this filter set.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">{{ $walletBalances->links() }}</div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Wallet Funding Report</strong>
                <span class="badge badge-light border">Visible Funding: PHP {{ number_format($fundingTotal, 2) }}</span>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Wallet</th>
                            <th>Branch</th>
                            <th>Provider</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($fundings as $funding)
                            <tr>
                                <td>{{ $funding->funding_number }}</td>
                                <td>{{ $funding->wallet?->wallet_number }}</td>
                                <td>{{ $funding->branch?->name }}</td>
                                <td>{{ $funding->provider?->provider_name }}</td>
                                <td>PHP {{ number_format($funding->amount, 2) }}</td>
                                <td><span class="badge badge-pill status-{{ $funding->status }}">{{ strtoupper($funding->status) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">No funding data for this filter set.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">{{ $fundings->links() }}</div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header"><strong>Wallet Ledger Report</strong></div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Wallet</th>
                            <th>Type</th>
                            <th>In</th>
                            <th>Out</th>
                            <th>Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ledgers as $ledger)
                            <tr>
                                <td>{{ $ledger->created_at }}</td>
                                <td>{{ $ledger->wallet?->wallet_number }}</td>
                                <td>{{ $ledger->movement_type }}</td>
                                <td>{{ number_format($ledger->amount_in, 2) }}</td>
                                <td>{{ number_format($ledger->amount_out, 2) }}</td>
                                <td>{{ number_format($ledger->running_balance, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">No ledger records for this filter set.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">{{ $ledgers->links() }}</div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header"><strong>Commission Report</strong></div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Transaction</th>
                            <th>Provider</th>
                            <th>Branch</th>
                            <th>Type</th>
                            <th>Value</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($commissions as $commission)
                            <tr>
                                <td>{{ $commission->created_at }}</td>
                                <td>{{ $commission->transaction?->transaction_number }}</td>
                                <td>{{ $commission->provider?->provider_name }}</td>
                                <td>{{ $commission->branch?->name }}</td>
                                <td>{{ ucfirst($commission->commission_type) }}</td>
                                <td>{{ number_format($commission->commission_value, 2) }}</td>
                                <td>PHP {{ number_format($commission->commission_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-3">No commission records for this filter set.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">{{ $commissions->links() }}</div>
        </div>
    </div>

    <div class="col-xl-3">
        <div class="card shadow-sm sticky-filter">
            <div class="card-header"><strong>Filter Panel</strong></div>
            <div class="card-body">
                <form method="GET" class="form-row">
                    <div class="col-12 mb-2">
                        <label class="small text-muted mb-1">Date From</label>
                        <input class="form-control" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}">
                    </div>
                    <div class="col-12 mb-2">
                        <label class="small text-muted mb-1">Date To</label>
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
                    <div class="col-12 mb-2">
                        <label class="small text-muted mb-1">Cashier</label>
                        <select class="form-control" name="cashier_id">
                            <option value="">All Cashiers</option>
                            @foreach($cashiers as $cashier)
                                <option value="{{ $cashier->id }}" @selected(($filters['cashier_id'] ?? null) == $cashier->id)>{{ $cashier->display_name }}</option>
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
                    <div class="col-12 mb-2">
                        <button class="btn btn-outline-primary btn-block">Apply Filters</button>
                    </div>
                    <div class="col-12">
                        <a href="{{ route('airtime.reports.index') }}" class="btn btn-light border btn-block">Reset Filters</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.kpi-tile {
    border: 1px solid #dbe3ec;
    border-radius: 12px;
    background: #fff;
    padding: 0.75rem;
    min-height: 88px;
}

.kpi-label {
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #6c757d;
}

.kpi-value {
    font-size: 1.2rem;
    font-weight: 700;
    margin-top: 0.3rem;
}

.status-successful,
.status-approved,
.status-active {
    background: #d4edda;
    color: #175c2c;
}

.status-pending {
    background: #fff3cd;
    color: #7c5700;
}

.status-failed,
.status-cancelled,
.status-rejected {
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
