<?php

namespace App\Http\Requests\Hr;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePositionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $positionId = $this->route('position')?->id ?? $this->route('position');

        return [
            'position_code' => ['required', 'string', 'max:60', Rule::unique('positions', 'position_code')->ignore($positionId)],
            'position_name' => ['required', 'string', 'max:150'],
            'department' => ['nullable', 'string', 'max:120'],
            'salary_type' => ['required', Rule::in(['monthly', 'daily', 'hourly'])],
            'default_salary_rate' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
