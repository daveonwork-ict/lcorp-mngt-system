@extends('layouts.app')
@section('page_title', $mode === 'create' ? 'Create Category' : 'Edit Category')
@section('content')
<form method="POST" action="{{ $mode === 'create' ? route('inventory.categories.store') : route('inventory.categories.update', $category) }}">
    @csrf @if($mode === 'edit') @method('PUT') @endif
    <div class="card"><div class="card-body">
        <div class="form-row">
            <div class="col-md-3"><label>Code</label><input class="form-control" name="category_code" value="{{ old('category_code', $category->category_code) }}" required></div>
            <div class="col-md-4"><label>Name</label><input class="form-control" name="category_name" value="{{ old('category_name', $category->category_name) }}" required></div>
            <div class="col-md-2"><label>Sort</label><input class="form-control" type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order) }}"></div>
            <div class="col-md-3"><label>Status</label><select class="form-control" name="status"><option value="active" @selected(old('status', $category->status ?: 'active')==='active')>Active</option><option value="inactive" @selected(old('status', $category->status)==='inactive')>Inactive</option></select></div>
        </div>
        <div class="form-group mt-3"><label>Description</label><textarea class="form-control" name="description">{{ old('description', $category->description) }}</textarea></div>
    </div><div class="card-footer text-right"><button class="btn btn-primary">Save</button></div></div>
</form>
@endsection
