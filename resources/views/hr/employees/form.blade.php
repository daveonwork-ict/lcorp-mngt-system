@extends('layouts.app')

@section('page_title', $mode === 'create' ? 'Create Employee Profile' : 'Edit Employee Profile')
@section('content')
<form method="POST" action="{{ $mode === 'create' ? route('hr.employees.store') : route('hr.employees.update', $employeeProfile) }}">
    @csrf
    @if ($mode === 'edit') @method('PUT') @endif
    <div class="card">
        <div class="card-body">
            <div class="form-row">
                <div class="col-md-4 mb-3"><label>User *</label><select name="user_id" class="form-control" required>@foreach($users as $user)<option value="{{ $user->id }}" @selected((int) old('user_id', $employeeProfile->user_id) === $user->id)>{{ $user->display_name }} ({{ $user->employee_code }})</option>@endforeach</select></div>
                <div class="col-md-4 mb-3"><label>Branch</label><select name="branch_id" class="form-control"><option value="">Select</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected((int) old('branch_id', $employeeProfile->branch_id) === $branch->id)>{{ $branch->branch_name ?? $branch->name }}</option>@endforeach</select></div>
                <div class="col-md-4 mb-3"><label>Position</label><select name="position_id" class="form-control"><option value="">Select</option>@foreach($positions as $position)<option value="{{ $position->id }}" @selected((int) old('position_id', $employeeProfile->position_id) === $position->id)>{{ $position->position_name }}</option>@endforeach</select></div>
            </div>
            <div class="form-row">
                <div class="col-md-3 mb-3"><label>Birthdate</label><input type="date" name="birthdate" class="form-control" value="{{ old('birthdate', optional($employeeProfile->birthdate)->format('Y-m-d')) }}"></div>
                <div class="col-md-3 mb-3"><label>Gender</label><select name="gender" class="form-control"><option value="">Select</option>@foreach(['male','female','other'] as $gender)<option value="{{ $gender }}" @selected(old('gender', $employeeProfile->gender) === $gender)>{{ ucfirst($gender) }}</option>@endforeach</select></div>
                <div class="col-md-3 mb-3"><label>Civil Status</label><select name="civil_status" class="form-control"><option value="">Select</option>@foreach(['single','married','widowed','separated'] as $civilStatus)<option value="{{ $civilStatus }}" @selected(old('civil_status', $employeeProfile->civil_status) === $civilStatus)>{{ ucfirst($civilStatus) }}</option>@endforeach</select></div>
                <div class="col-md-3 mb-3"><label>Employment Date</label><input type="date" name="employment_date" class="form-control" value="{{ old('employment_date', optional($employeeProfile->employment_date)->format('Y-m-d')) }}"></div>
            </div>
            <div class="form-row">
                <div class="col-md-3 mb-3"><label>Employment Type *</label><select name="employment_type" class="form-control" required>@foreach(['regular','probationary','contractual','project_based','part_time','casual'] as $type)<option value="{{ $type }}" @selected(old('employment_type', $employeeProfile->employment_type ?: 'regular') === $type)>{{ ucfirst(str_replace('_',' ', $type)) }}</option>@endforeach</select></div>
                <div class="col-md-3 mb-3"><label>Employment Status *</label><select name="employment_status" class="form-control" required>@foreach(['active','inactive','resigned','terminated','on_leave'] as $status)<option value="{{ $status }}" @selected(old('employment_status', $employeeProfile->employment_status ?: 'active') === $status)>{{ ucfirst(str_replace('_',' ', $status)) }}</option>@endforeach</select></div>
                <div class="col-md-3 mb-3"><label>Salary Type *</label><select name="salary_type" class="form-control" required>@foreach(['monthly','daily','hourly'] as $type)<option value="{{ $type }}" @selected(old('salary_type', $employeeProfile->salary_type ?: 'monthly') === $type)>{{ ucfirst($type) }}</option>@endforeach</select></div>
                <div class="col-md-3 mb-3"><label>Salary Rate *</label><input type="number" min="0" step="0.01" name="salary_rate" class="form-control" value="{{ old('salary_rate', $employeeProfile->salary_rate ?: 0) }}" required></div>
            </div>
            <div class="form-row">
                <div class="col-md-6 mb-3"><label>Address</label><input name="address" class="form-control" value="{{ old('address', $employeeProfile->address) }}"></div>
                <div class="col-md-3 mb-3"><label>Emergency Contact Name</label><input name="emergency_contact_name" class="form-control" value="{{ old('emergency_contact_name', $employeeProfile->emergency_contact_name) }}"></div>
                <div class="col-md-3 mb-3"><label>Emergency Contact Number</label><input name="emergency_contact_number" class="form-control" value="{{ old('emergency_contact_number', $employeeProfile->emergency_contact_number) }}"></div>
            </div>
        </div>
        <div class="card-footer text-right">
            <a href="{{ route('hr.employees.index') }}" class="btn btn-default">Cancel</a>
            <button class="btn btn-primary">Save Profile</button>
        </div>
    </div>
</form>
@endsection
