<?php

namespace App\Http\Requests\Branch;

use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBranchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'branch_code' => ['required', 'string', 'max:100', 'unique:branches,branch_code'],
            'branch_name' => ['required', 'string', 'max:150'],
            'address' => ['nullable', 'string', 'max:250'],
            'contact_number' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:190'],
            'manager_id' => ['nullable', 'integer', 'exists:users,id'],
            'opening_time' => ['nullable', 'date_format:H:i'],
            'closing_time' => ['nullable', 'date_format:H:i'],
            'operational_status' => ['required', Rule::in(['active', 'inactive', 'maintenance', 'closed'])],
            'status' => ['required', Rule::in(['active', 'inactive', 'maintenance', 'closed'])],
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ];
    }
}
