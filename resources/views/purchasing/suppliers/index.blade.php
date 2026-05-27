@extends('layouts.app')

@section('page_title', 'Suppliers')
@section('content')
<div class="card mb-3">
    <div class="card-header">Add Supplier</div>
    <div class="card-body">
        <form method="POST" action="{{ route('suppliers.store') }}" class="form-row">@csrf
            <div class="col-md-3 mb-2"><input class="form-control" name="supplier_name" placeholder="Supplier name" required></div>
            <div class="col-md-2 mb-2"><input class="form-control" name="contact_person" placeholder="Contact person"></div>
            <div class="col-md-2 mb-2"><input class="form-control" name="contact_number" placeholder="Contact #"></div>
            <div class="col-md-2 mb-2"><input class="form-control" name="email" placeholder="Email"></div>
            <div class="col-md-2 mb-2"><input class="form-control" name="payment_terms" placeholder="Payment terms"></div>
            <div class="col-md-1 mb-2"><button class="btn btn-primary btn-block">Save</button></div>
            <input type="hidden" name="status" value="active">
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">Supplier Registry</div>
    <div class="table-responsive p-0">
        <table class="table table-sm mb-0">
            <thead><tr><th>Code</th><th>Name</th><th>Contact</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse($suppliers as $supplier)
                <tr>
                    <td>{{ $supplier->supplier_code }}</td>
                    <td>{{ $supplier->supplier_name }}</td>
                    <td>{{ $supplier->contact_person }}<br><small>{{ $supplier->contact_number }}</small></td>
                    <td>{{ ucfirst($supplier->status) }}</td>
                    <td><a href="{{ route('suppliers.show', $supplier) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted">No suppliers found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $suppliers->links() }}</div>
</div>
@endsection
