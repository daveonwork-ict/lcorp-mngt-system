@extends('layouts.app')
@section('page_title', 'Brands')
@section('content')
<div class="card">
    <div class="card-header text-right"><a class="btn btn-primary" href="{{ route('inventory.brands.create') }}">Create Brand</a></div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover"><thead><tr><th>Code</th><th>Name</th><th>Status</th><th></th></tr></thead><tbody>
            @foreach($brands as $brand)
            <tr><td>{{ $brand->brand_code }}</td><td>{{ $brand->brand_name }}</td><td>{{ ucfirst($brand->status) }}</td><td><a href="{{ route('inventory.brands.edit', $brand) }}" class="btn btn-xs btn-outline-primary">Edit</a></td></tr>
            @endforeach
        </tbody></table>
    </div>
    <div class="card-footer">{{ $brands->links() }}</div>
</div>
@endsection
