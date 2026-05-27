@extends('layouts.app')
@section('page_title', 'Product Categories')
@section('content')
<div class="card">
    <div class="card-header text-right"><a class="btn btn-primary" href="{{ route('inventory.categories.create') }}">Create Category</a></div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover"><thead><tr><th>Code</th><th>Name</th><th>Sort</th><th>Status</th><th></th></tr></thead><tbody>
            @foreach($categories as $category)
            <tr>
                <td>{{ $category->category_code }}</td><td>{{ $category->category_name }}</td><td>{{ $category->sort_order }}</td><td>{{ ucfirst($category->status) }}</td>
                <td><a href="{{ route('inventory.categories.edit', $category) }}" class="btn btn-xs btn-outline-primary">Edit</a></td>
            </tr>
            @endforeach
        </tbody></table>
    </div>
    <div class="card-footer">{{ $categories->links() }}</div>
</div>
@endsection
