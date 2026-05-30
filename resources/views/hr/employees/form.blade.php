@extends('layouts.app')

@section('page_title', $mode === 'create' ? 'Create Employee Profile' : 'Edit Employee Profile')
@section('content')
<form method="POST" action="{{ $mode === 'create' ? route('hr.employees.store') : route('hr.employees.update', $employeeProfile) }}">
    @csrf
    @if ($mode === 'edit') @method('PUT') @endif
    <div class="card">
        <div class="card-body">
            <div class="form-row">
                <div class="col-md-4 mb-3"><label>Existing User</label><select name="user_id" class="form-control"><option value="">Create employee login below</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected((int) old('user_id', $employeeProfile->user_id) === $user->id)>{{ $user->display_name }} ({{ $user->employee_code }})</option>@endforeach</select></div>
                <div class="col-md-4 mb-3"><label>Branch</label><select name="branch_id" class="form-control"><option value="">Select</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected((int) old('branch_id', $employeeProfile->branch_id) === $branch->id)>{{ $branch->branch_name ?? $branch->name }}</option>@endforeach</select></div>
                <div class="col-md-4 mb-3"><label>Position</label><select name="position_id" class="form-control"><option value="">Select</option>@foreach($positions as $position)<option value="{{ $position->id }}" @selected((int) old('position_id', $employeeProfile->position_id) === $position->id)>{{ $position->position_name }}</option>@endforeach</select></div>
            </div>
            <div class="card card-outline card-info mb-3">
                <div class="card-header"><strong>Employee Login Account</strong></div>
                <div class="card-body">
                    <div class="form-group form-check">
                        <input type="checkbox" class="form-check-input" id="create_user_account" name="create_user_account" value="1" @checked(old('create_user_account') || ($mode === 'edit' && $employeeProfile->user_id))>
                        <label class="form-check-label" for="create_user_account">Create or update employee self-service login</label>
                    </div>
                    <div class="form-row">
                        <div class="col-md-3 mb-3"><label>First Name</label><input name="account_first_name" class="form-control employee-account-field" value="{{ old('account_first_name', $employeeProfile->user?->first_name) }}"></div>
                        <div class="col-md-3 mb-3"><label>Last Name</label><input name="account_last_name" class="form-control employee-account-field" value="{{ old('account_last_name', $employeeProfile->user?->last_name) }}"></div>
                        <div class="col-md-3 mb-3"><label>Email</label><input type="email" name="account_email" class="form-control employee-account-field" value="{{ old('account_email', $employeeProfile->user?->email) }}"></div>
                        <div class="col-md-3 mb-3"><label>Username</label><input name="account_username" class="form-control employee-account-field" value="{{ old('account_username', $employeeProfile->user?->username) }}"></div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-4 mb-3"><label>Mobile Number</label><input name="account_mobile_number" class="form-control employee-account-field" value="{{ old('account_mobile_number', $employeeProfile->user?->mobile_number) }}"></div>
                        <div class="col-md-4 mb-3"><label>Employee Code</label><input name="account_employee_code" class="form-control employee-account-field" value="{{ old('account_employee_code', $employeeProfile->user?->employee_code) }}"></div>
                        <div class="col-md-4 mb-3"><label>Password {{ $mode === 'create' ? '*' : '' }}</label><input type="password" name="account_password" class="form-control employee-account-field"></div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-4 mb-3"><label>Confirm Password</label><input type="password" name="account_password_confirmation" class="form-control employee-account-field"></div>
                        <div class="col-md-8 mb-3 d-flex align-items-end"><small class="text-muted">Uses the Staff User role so the employee can log in for attendance selfie in/out, payslips, leave, overtime, announcements, and chat.</small></div>
                    </div>
                </div>
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

@push('scripts')
<script>
(function () {
    const toggle = document.getElementById('create_user_account');
    const existingUser = document.querySelector('select[name="user_id"]');
    const fields = document.querySelectorAll('.employee-account-field');

    const syncState = function () {
        const checked = !!(toggle && toggle.checked);

        if (existingUser) {
            existingUser.disabled = checked;
        }

        fields.forEach(function (field) {
            field.disabled = !checked;
        });
    };

    if (toggle) {
        toggle.addEventListener('change', syncState);
        syncState();
    }
})();
</script>
@endpush
