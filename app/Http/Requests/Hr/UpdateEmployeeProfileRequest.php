<?php

namespace App\Http\Requests\Hr;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $profileId = $this->route('employee')?->id ?? $this->route('employee');
        $currentUserId = $this->route('employee')?->user_id;

        return [
            'user_id' => ['nullable', 'integer', 'exists:users,id', Rule::unique('employee_profiles', 'user_id')->ignore($profileId), 'required_without:create_user_account'],
            'create_user_account' => ['nullable', 'boolean'],
            'account_first_name' => ['nullable', 'string', 'max:120', 'required_if:create_user_account,1'],
            'account_last_name' => ['nullable', 'string', 'max:120', 'required_if:create_user_account,1'],
            'account_email' => ['nullable', 'email', 'max:150', 'required_if:create_user_account,1', Rule::unique('users', 'email')->ignore($currentUserId)],
            'account_username' => ['nullable', 'string', 'max:120', 'required_if:create_user_account,1', Rule::unique('users', 'username')->ignore($currentUserId)],
            'account_mobile_number' => ['nullable', 'string', 'max:50'],
            'account_employee_code' => ['nullable', 'string', 'max:120', Rule::unique('users', 'employee_code')->ignore($currentUserId)],
            'account_password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'position_id' => ['nullable', 'integer', 'exists:positions,id'],
            'birthdate' => ['nullable', 'date'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'civil_status' => ['nullable', Rule::in(['single', 'married', 'widowed', 'separated'])],
            'address' => ['nullable', 'string'],
            'emergency_contact_name' => ['nullable', 'string', 'max:150'],
            'emergency_contact_number' => ['nullable', 'string', 'max:50'],
            'employment_date' => ['nullable', 'date'],
            'employment_type' => ['required', Rule::in(['regular', 'probationary', 'contractual', 'project_based', 'part_time', 'casual'])],
            'employment_status' => ['required', Rule::in(['active', 'inactive', 'resigned', 'terminated', 'on_leave'])],
            'salary_type' => ['required', Rule::in(['monthly', 'daily', 'hourly'])],
            'salary_rate' => ['required', 'numeric', 'min:0'],
        ];
    }
}
