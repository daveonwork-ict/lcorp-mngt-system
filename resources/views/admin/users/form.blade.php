@extends('layouts.app')

@section('page_title', $mode === 'create' ? 'Create User' : 'Edit User')
@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
<li class="breadcrumb-item active">{{ $mode === 'create' ? 'Create' : 'Edit' }}</li>
@endsection

@section('content')
<form method="POST" action="{{ $mode === 'create' ? route('admin.users.store') : route('admin.users.update', $user) }}" enctype="multipart/form-data">
    @csrf
    @if ($mode === 'edit') @method('PUT') @endif
    <div class="card">
        <div class="card-body">
            <div class="form-row">
                <div class="col-md-3 mb-3"><label>Employee Code *</label><input name="employee_code" class="form-control" value="{{ old('employee_code', $user->employee_code) }}" required></div>
                <div class="col-md-3 mb-3"><label>Username *</label><input name="username" class="form-control" value="{{ old('username', $user->username) }}" required></div>
                <div class="col-md-3 mb-3"><label>Email *</label><input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required></div>
                <div class="col-md-3 mb-3"><label>Mobile</label><input name="mobile_number" class="form-control" value="{{ old('mobile_number', $user->mobile_number) }}"></div>
            </div>
            <div class="form-row">
                <div class="col-md-3 mb-3"><label>First Name *</label><input name="first_name" class="form-control" value="{{ old('first_name', $user->first_name) }}" required></div>
                <div class="col-md-3 mb-3"><label>Middle Name</label><input name="middle_name" class="form-control" value="{{ old('middle_name', $user->middle_name) }}"></div>
                <div class="col-md-3 mb-3"><label>Last Name *</label><input name="last_name" class="form-control" value="{{ old('last_name', $user->last_name) }}" required></div>
                <div class="col-md-3 mb-3"><label>Suffix</label><input name="suffix" class="form-control" value="{{ old('suffix', $user->suffix) }}"></div>
            </div>
            <div class="form-row">
                <div class="col-md-4 mb-3"><label>Password {{ $mode === 'create' ? '*' : '' }}</label><input type="password" name="password" class="form-control" {{ $mode === 'create' ? 'required' : '' }}></div>
                <div class="col-md-4 mb-3"><label>Role *</label><select name="role_id" class="form-control" required>@foreach($roles as $role)<option value="{{ $role->id }}" @selected((int) old('role_id', $user->role_id) === $role->id)>{{ $role->name }}</option>@endforeach</select></div>
                <div class="col-md-4 mb-3"><label>Status *</label><select name="status" class="form-control" required>@foreach(['active','inactive','suspended','locked'] as $s)<option value="{{ $s }}" @selected(old('status', $user->status ?: 'active') === $s)>{{ ucfirst($s) }}</option>@endforeach</select></div>
            </div>
            <div class="form-row">
                <div class="col-md-4 mb-3"><label>Primary Branch</label><select name="primary_branch_id" class="form-control"><option value="">Select</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected((int) old('primary_branch_id', $user->primary_branch_id) === $branch->id)>{{ $branch->name }}</option>@endforeach</select></div>
                <div class="col-md-6 mb-3"><label>Assigned Branches</label><select name="branch_ids[]" class="form-control" multiple size="4">@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected(collect(old('branch_ids', $user->branches->pluck('id')->toArray()))->contains($branch->id))>{{ $branch->name }}</option>@endforeach</select></div>
                <div class="col-md-2 mb-3"><label>Photo</label><input type="file" name="profile_photo" class="form-control-file"></div>
            </div>
        </div>
        <div class="card-footer text-right">
            <a href="{{ route('admin.users.index') }}" class="btn btn-default">Cancel</a>
            <button class="btn btn-primary">Save User</button>
        </div>
    </div>
</form>
@endsection
