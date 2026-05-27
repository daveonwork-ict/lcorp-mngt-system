@extends('layouts.app')
@section('page_title', $mode === 'create' ? 'Create Brand' : 'Edit Brand')
@section('content')
<form method="POST" action="{{ $mode === 'create' ? route('inventory.brands.store') : route('inventory.brands.update', $brand) }}">
    @csrf @if($mode==='edit') @method('PUT') @endif
    <div class="card"><div class="card-body"><div class="form-row">
        <div class="col-md-4"><label>Code</label><input class="form-control" name="brand_code" value="{{ old('brand_code', $brand->brand_code) }}" required></div>
        <div class="col-md-4"><label>Name</label><input class="form-control" name="brand_name" value="{{ old('brand_name', $brand->brand_name) }}" required></div>
        <div class="col-md-4"><label>Status</label><select class="form-control" name="status"><option value="active" @selected(old('status', $brand->status ?: 'active')==='active')>Active</option><option value="inactive" @selected(old('status', $brand->status)==='inactive')>Inactive</option></select></div>
    </div><div class="form-group mt-3"><label>Description</label><textarea class="form-control" name="description">{{ old('description', $brand->description) }}</textarea></div></div><div class="card-footer text-right"><button class="btn btn-primary">Save</button></div></div>
</form>
@endsection
