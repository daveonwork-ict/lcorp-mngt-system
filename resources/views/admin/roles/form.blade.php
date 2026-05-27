@extends('layouts.app')

@section('page_title', $mode === 'create' ? 'Create Role' : 'Edit Role')
@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.roles.index') }}">Roles</a></li>
<li class="breadcrumb-item active">{{ $mode === 'create' ? 'Create' : 'Edit' }}</li>
@endsection

@section('content')
<form method="POST" action="{{ $mode === 'create' ? route('admin.roles.store') : route('admin.roles.update', $role) }}">
    @csrf
    @if($mode === 'edit') @method('PUT') @endif
    <div class="card">
        <div class="card-body">
            <div class="form-row">
                <div class="col-md-4"><label>Code *</label><input name="code" class="form-control" value="{{ old('code', $role->code) }}" required></div>
                <div class="col-md-4"><label>Name *</label><input name="name" class="form-control" value="{{ old('name', $role->name) }}" required></div>
                <div class="col-md-4"><label>Status *</label><select name="status" class="form-control">@foreach(['active','inactive'] as $s)<option value="{{ $s }}" @selected(old('status', $role->status ?: 'active') === $s)>{{ ucfirst($s) }}</option>@endforeach</select></div>
            </div>
            <div class="form-group mt-3"><label>Description</label><textarea name="description" class="form-control" rows="2">{{ old('description', $role->description) }}</textarea></div>
            <div class="form-group">
                <label>Assign Permissions</label>
                @foreach($permissions as $module => $items)
                    <div class="border rounded p-2 mb-2">
                        <strong class="text-capitalize">{{ str_replace('_', ' ', $module) }}</strong>
                        <div class="row">
                            @foreach($items as $permission)
                                <div class="col-md-4">
                                    <div class="custom-control custom-checkbox">
                                        <input class="custom-control-input" type="checkbox" id="perm{{ $permission->id }}" name="permission_ids[]" value="{{ $permission->id }}" @checked(collect(old('permission_ids', $role->permissions->pluck('id')->toArray()))->contains($permission->id))>
                                        <label class="custom-control-label" for="perm{{ $permission->id }}">{{ $permission->name }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="card-footer text-right"><button class="btn btn-primary">Save Role</button></div>
    </div>
</form>
@endsection
