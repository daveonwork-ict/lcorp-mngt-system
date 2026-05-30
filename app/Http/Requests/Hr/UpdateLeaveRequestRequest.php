<?php

namespace App\Http\Requests\Hr;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLeaveRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'leave_type' => ['required', Rule::in(['vacation', 'sick', 'emergency', 'maternity', 'paternity', 'bereavement', 'without_pay'])],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string'],
            'status' => ['nullable', Rule::in(['pending_manager', 'pending_hr', 'approved', 'rejected', 'cancelled'])],
            'final_remarks' => ['nullable', 'string'],
        ];
    }
}
