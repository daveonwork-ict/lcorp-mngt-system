@extends('layouts.app')

@section('page_title', $mode === 'create' ? 'Create Branch' : 'Edit Branch')
@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.branches.index') }}">Branches</a></li>
<li class="breadcrumb-item active">{{ $mode === 'create' ? 'Create' : 'Edit' }}</li>
@endsection

@section('content')
<form method="POST" action="{{ $mode === 'create' ? route('admin.branches.store') : route('admin.branches.update', $branch) }}">
    @csrf
    @if ($mode === 'edit') @method('PUT') @endif
    <div class="card">
        <div class="card-body">
            <div class="form-row">
                <div class="col-md-4 mb-3"><label>Branch Code *</label><input name="branch_code" class="form-control" value="{{ old('branch_code', $branch->branch_code) }}" required></div>
                <div class="col-md-4 mb-3"><label>Branch Name *</label><input name="branch_name" class="form-control" value="{{ old('branch_name', $branch->branch_name) }}" required></div>
                <div class="col-md-4 mb-3"><label>Contact Number</label><input name="contact_number" class="form-control" value="{{ old('contact_number', $branch->contact_number) }}"></div>
            </div>
            <div class="form-row">
                <div class="col-md-4 mb-3"><label>Email</label><input type="email" name="email" class="form-control" value="{{ old('email', $branch->email) }}"></div>
                <div class="col-md-8 mb-3"><label>Address</label><input name="address" class="form-control" value="{{ old('address', $branch->address) }}"></div>
            </div>
            <div class="form-row">
                <div class="col-md-3 mb-3"><label>Opening Time</label><input type="time" name="opening_time" class="form-control" value="{{ old('opening_time', $branch->opening_time) }}"></div>
                <div class="col-md-3 mb-3"><label>Closing Time</label><input type="time" name="closing_time" class="form-control" value="{{ old('closing_time', $branch->closing_time) }}"></div>
                <div class="col-md-3 mb-3"><label>Operational Status</label><select name="operational_status" class="form-control">@foreach(['active','inactive','maintenance','closed'] as $s)<option value="{{ $s }}" @selected(old('operational_status', $branch->operational_status ?: 'active') === $s)>{{ ucfirst($s) }}</option>@endforeach</select></div>
                <div class="col-md-3 mb-3"><label>Status</label><select name="status" class="form-control">@foreach(['active','inactive','maintenance','closed'] as $s)<option value="{{ $s }}" @selected(old('status', $branch->status ?: 'active') === $s)>{{ ucfirst($s) }}</option>@endforeach</select></div>
            </div>
            <div class="form-row">
                <div class="col-md-4 mb-3"><label>Manager</label><select name="manager_id" class="form-control"><option value="">Select manager</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected((int) old('manager_id', $branch->manager_id) === $user->id)>{{ $user->display_name }}</option>@endforeach</select></div>
                <div class="col-md-8 mb-3"><label>Assigned Users</label><select name="user_ids[]" class="form-control" multiple size="5">@foreach($users as $user)<option value="{{ $user->id }}" @selected(collect(old('user_ids', $branch->users->pluck('id')->toArray()))->contains($user->id))>{{ $user->display_name }}</option>@endforeach</select></div>
            </div>
        </div>
        <div class="card-footer text-right"><button class="btn btn-primary">Save Branch</button></div>
    </div>
</form>
@endsection
