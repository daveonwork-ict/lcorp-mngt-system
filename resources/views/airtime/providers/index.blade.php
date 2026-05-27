@extends('layouts.app')

@section('page_title', 'Airtime Providers')
@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">Create Provider</div>
            <div class="card-body">
                <form method="POST" action="{{ route('airtime.providers.store') }}">
                    @csrf
                    <div class="form-group"><label>Code</label><input class="form-control" name="provider_code" required></div>
                    <div class="form-group"><label>Name</label><input class="form-control" name="provider_name" required></div>
                    <div class="form-group"><label>Description</label><textarea class="form-control" name="description"></textarea></div>
                    <div class="form-group"><label>Commission Type</label><select class="form-control" name="default_commission_type"><option value="none">None</option><option value="fixed">Fixed</option><option value="percentage">Percentage</option></select></div>
                    <div class="form-group"><label>Commission Value</label><input class="form-control" type="number" step="0.01" name="default_commission_value" value="0"></div>
                    <div class="form-group"><label>Status</label><select class="form-control" name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                    <button class="btn btn-primary btn-block">Save Provider</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">Provider List</div>
            <div class="card-body table-responsive p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Code</th><th>Name</th><th>Commission</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @foreach($providers as $provider)
                            <tr>
                                <td>{{ $provider->provider_code }}</td>
                                <td>{{ $provider->provider_name }}</td>
                                <td>{{ ucfirst($provider->default_commission_type) }} {{ $provider->default_commission_value }}</td>
                                <td><span class="badge badge-{{ $provider->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($provider->status) }}</span></td>
                                <td>
                                    <form method="POST" action="{{ route('airtime.providers.update', $provider) }}" class="form-inline">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="provider_code" value="{{ $provider->provider_code }}">
                                        <input type="hidden" name="provider_name" value="{{ $provider->provider_name }}">
                                        <input type="hidden" name="description" value="{{ $provider->description }}">
                                        <input type="hidden" name="default_commission_type" value="{{ $provider->default_commission_type }}">
                                        <input type="hidden" name="default_commission_value" value="{{ $provider->default_commission_value }}">
                                        <select class="form-control form-control-sm mr-1" name="status"><option value="active" @selected($provider->status==='active')>Active</option><option value="inactive" @selected($provider->status==='inactive')>Inactive</option></select>
                                        <button class="btn btn-xs btn-outline-primary">Save</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer">{{ $providers->links() }}</div>
        </div>
    </div>
</div>
@endsection
