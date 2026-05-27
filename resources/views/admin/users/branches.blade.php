@extends('layouts.app')

@section('page_title', 'User Branch Assignment')
@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
<li class="breadcrumb-item active">Branches</li>
@endsection

@section('content')
<form method="POST" action="{{ route('admin.users.branches.update', $user) }}">
    @csrf
    @method('PUT')
    <div class="card">
        <div class="card-body">
            <div class="form-group">
                <label>Assigned Branches *</label>
                <select name="branch_ids[]" class="form-control" multiple size="8" required>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" @selected($user->branches->pluck('id')->contains($branch->id))>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Primary Branch</label>
                <select name="primary_branch_id" class="form-control">
                    <option value="">Select</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((int) $user->primary_branch_id === $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="card-footer text-right">
            <button class="btn btn-primary">Save Assignment</button>
        </div>
    </div>
</form>
@endsection
