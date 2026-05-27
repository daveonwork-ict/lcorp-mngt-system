@extends('layouts.app')

@section('page_title', 'Permission Management')
@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard.owner') }}">Dashboard</a></li>
<li class="breadcrumb-item active">Permissions</li>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        @foreach($groupedPermissions as $module => $permissions)
            <h5 class="text-capitalize">{{ str_replace('_', ' ', $module) }}</h5>
            <div class="table-responsive mb-3">
                <table class="table table-bordered table-sm">
                    <thead><tr><th>Code</th><th>Name</th><th>Description</th></tr></thead>
                    <tbody>
                    @foreach($permissions as $permission)
                        <tr><td>{{ $permission->code }}</td><td>{{ $permission->name }}</td><td>{{ $permission->description }}</td></tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    </div>
</div>
@endsection
