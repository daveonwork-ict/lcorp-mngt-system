@extends('layouts.app')

@section('page_title', $mode === 'create' ? 'Create HR Position' : 'Edit HR Position')
@section('content')
<form method="POST" action="{{ $mode === 'create' ? route('hr.positions.store') : route('hr.positions.update', $position) }}">
    @csrf
    @if ($mode === 'edit') @method('PUT') @endif
    <div class="card">
        <div class="card-body">
            <div class="form-row">
                <div class="col-md-3 mb-3"><label>Position Code *</label><input name="position_code" class="form-control" value="{{ old('position_code', $position->position_code) }}" required></div>
                <div class="col-md-5 mb-3"><label>Position Name *</label><input name="position_name" class="form-control" value="{{ old('position_name', $position->position_name) }}" required></div>
                <div class="col-md-4 mb-3"><label>Department</label><input name="department" class="form-control" value="{{ old('department', $position->department) }}"></div>
            </div>
            <div class="form-row">
                <div class="col-md-4 mb-3"><label>Salary Type *</label><select name="salary_type" class="form-control" required>@foreach(['monthly','daily','hourly'] as $type)<option value="{{ $type }}" @selected(old('salary_type', $position->salary_type ?: 'monthly') === $type)>{{ ucfirst($type) }}</option>@endforeach</select></div>
                <div class="col-md-4 mb-3"><label>Default Salary Rate *</label><input type="number" step="0.01" min="0" name="default_salary_rate" class="form-control" value="{{ old('default_salary_rate', $position->default_salary_rate ?: 0) }}" required></div>
                <div class="col-md-4 mb-3"><label>Status *</label><select name="status" class="form-control" required>@foreach(['active','inactive'] as $status)<option value="{{ $status }}" @selected(old('status', $position->status ?: 'active') === $status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
            </div>
        </div>
        <div class="card-footer text-right">
            <a href="{{ route('hr.positions.index') }}" class="btn btn-default">Cancel</a>
            <button class="btn btn-primary">Save Position</button>
        </div>
    </div>
</form>
@endsection
