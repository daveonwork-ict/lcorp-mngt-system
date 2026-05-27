@extends('layouts.app')

@section('page_title', 'Payment Method Setup')
@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">Create Payment Method</div>
            <div class="card-body">
                <form method="POST" action="{{ route('sales.payment-methods.store') }}">
                    @csrf
                    <div class="form-group"><label>Name</label><input class="form-control" name="payment_method_name" required></div>
                    <div class="form-group"><label>Type</label><select class="form-control" name="payment_type"><option>Cash</option><option>E-Wallet</option><option>Bank</option><option>Card</option><option>Other</option></select></div>
                    <div class="form-group"><label><input type="checkbox" name="requires_reference" value="1"> Requires Reference</label></div>
                    <div class="form-group"><label>Status</label><select class="form-control" name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                    <button class="btn btn-primary btn-block">Save</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">Methods</div>
            <div class="card-body table-responsive p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Name</th><th>Type</th><th>Reference</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @foreach($methods as $method)
                            <tr>
                                <td>{{ $method->payment_method_name }}</td>
                                <td>{{ $method->payment_type }}</td>
                                <td>{{ $method->requires_reference ? 'Yes' : 'No' }}</td>
                                <td>{{ ucfirst($method->status) }}</td>
                                <td>
                                    <form method="POST" action="{{ route('sales.payment-methods.update', $method) }}" class="form-inline">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="payment_method_name" value="{{ $method->payment_method_name }}">
                                        <input type="hidden" name="payment_type" value="{{ $method->payment_type }}">
                                        <input type="hidden" name="requires_reference" value="{{ $method->requires_reference ? 1 : 0 }}">
                                        <select class="form-control form-control-sm mr-1" name="status"><option value="active" @selected($method->status === 'active')>Active</option><option value="inactive" @selected($method->status === 'inactive')>Inactive</option></select>
                                        <button class="btn btn-xs btn-outline-primary">Save</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer">{{ $methods->links() }}</div>
        </div>
    </div>
</div>
@endsection
