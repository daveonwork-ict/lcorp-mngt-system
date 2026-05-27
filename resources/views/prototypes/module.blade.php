@extends('layouts.app')

@section('page_title', $meta['name'].' Prototype')
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard.owner') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">{{ $meta['name'] }}</li>
@endsection

@section('content')
@if ($meta['slug'] === 'pos')
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Touchscreen POS</h3></div>
                <div class="card-body">
                    <div class="form-row mb-3">
                        <div class="col-md-8"><input class="form-control form-control-lg" placeholder="Search product / scan barcode"></div>
                        <div class="col-md-4"><input class="form-control form-control-lg" placeholder="Barcode input area"></div>
                    </div>
                    <div class="module-grid mb-3">
                        @for ($i = 1; $i <= 8; $i++)
                            <button class="btn btn-outline-primary touch-btn">Product {{ $i }}</button>
                        @endfor
                    </div>
                    <div class="btn-group d-flex">
                        <button class="btn btn-success touch-btn">Checkout</button>
                        <button class="btn btn-warning touch-btn">Hold</button>
                        <button class="btn btn-danger touch-btn">Clear Cart</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Cart / Receipt Preview</h3></div>
                <div class="card-body">
                    <p class="text-muted">Quantity controls, discount, payment method, and receipt preview are ready for dynamic binding.</p>
                    <textarea class="form-control" rows="11" readonly>Demo Receipt Preview\n----------------\nItem A x1\nItem B x2\nTotal: PHP 350.00</textarea>
                </div>
            </div>
        </div>
    </div>
@else
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ $meta['name'] }} Screens</h3>
            <div class="card-tools">
                @foreach ($filters as $filter)
                    <span class="badge badge-light border mr-1">{{ $filter }}</span>
                @endforeach
            </div>
        </div>
        <div class="card-body">
            <div class="module-grid mb-3">
                @foreach ($meta['screens'] as $screen)
                    <div class="card mb-0 border h-100">
                        <div class="card-body d-flex flex-column">
                            <h5>{{ $screen }}</h5>
                            <p class="text-muted small mb-0">Prototype section prepared for API-backed data and permission-aware actions.</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Branch</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>DEMO-001</td>
                        <td>Main Branch</td>
                        <td><span class="badge badge-info">Open</span></td>
                        <td><button class="btn btn-sm btn-outline-primary">View</button></td>
                    </tr>
                    <tr>
                        <td>DEMO-002</td>
                        <td>North Branch</td>
                        <td><span class="badge badge-success">Posted</span></td>
                        <td><button class="btn btn-sm btn-outline-secondary">Audit</button></td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                @foreach ($actions as $action)
                    <button class="btn btn-outline-dark btn-sm mr-2">{{ $action }}</button>
                @endforeach
            </div>
        </div>
    </div>
@endif
@endsection
