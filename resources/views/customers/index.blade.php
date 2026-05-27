@extends('layouts.app')

@section('page_title', 'Customers')
@section('content')
<div class="card mb-3">
    <div class="card-header">Customer Filters</div>
    <div class="card-body">
        <form method="GET" class="form-row">
            <div class="col-md-6"><input class="form-control" name="search" placeholder="Search name, mobile, code" value="{{ $filters['search'] ?? '' }}"></div>
            <div class="col-md-3"><select class="form-control" name="status"><option value="">All status</option><option value="active" @selected(($filters['status'] ?? null)==='active')>Active</option><option value="inactive" @selected(($filters['status'] ?? null)==='inactive')>Inactive</option><option value="blocklisted" @selected(($filters['status'] ?? null)==='blocklisted')>Blocklisted</option></select></div>
            <div class="col-md-3"><button class="btn btn-outline-primary btn-block">Apply</button></div>
        </form>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">Create Customer</div>
    <div class="card-body">
        <form method="POST" action="{{ route('customers.store') }}" class="form-row">
            @csrf
            <div class="col-md-3"><input class="form-control" name="first_name" placeholder="First name" required></div>
            <div class="col-md-2"><input class="form-control" name="middle_name" placeholder="Middle name"></div>
            <div class="col-md-3"><input class="form-control" name="last_name" placeholder="Last name" required></div>
            <div class="col-md-2"><input class="form-control" name="suffix" placeholder="Suffix"></div>
            <div class="col-md-2"><input class="form-control" name="mobile_number" placeholder="Mobile" required></div>
            <div class="col-md-3 mt-2"><input class="form-control" name="email" placeholder="Email"></div>
            <div class="col-md-4 mt-2"><input class="form-control" name="address" placeholder="Address"></div>
            <div class="col-md-2 mt-2"><select class="form-control" name="customer_type"><option value="walk_in">Walk-in</option><option value="regular">Regular</option><option value="vip">VIP</option><option value="corporate">Corporate</option><option value="online_customer">Online Customer</option></select></div>
            <div class="col-md-2 mt-2"><select class="form-control" name="status"><option value="active">Active</option><option value="inactive">Inactive</option><option value="blocklisted">Blocklisted</option></select></div>
            <div class="col-md-1 mt-2"><button class="btn btn-primary btn-block">Save</button></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body table-responsive p-0">
        <table class="table table-sm mb-0">
            <thead><tr><th>Code</th><th>Name</th><th>Mobile</th><th>Type</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            @foreach($customers as $customer)
                <tr>
                    <td>{{ $customer->customer_code }}</td>
                    <td><a href="{{ route('customers.profile', $customer) }}">{{ $customer->full_name }}</a></td>
                    <td>{{ $customer->mobile_number }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $customer->customer_type)) }}</td>
                    <td><span class="badge badge-{{ $customer->status==='active'?'success':($customer->status==='inactive'?'secondary':'danger') }}">{{ ucfirst($customer->status) }}</span></td>
                    <td>
                        <form method="POST" action="{{ route('customers.deactivate', $customer) }}" class="d-inline">
                            @csrf
                            <button class="btn btn-xs btn-warning">Deactivate</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $customers->links() }}</div>
</div>
@endsection
