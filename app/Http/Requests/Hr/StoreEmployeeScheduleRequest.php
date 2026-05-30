<?php

namespace App\Http\Requests\Hr;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeScheduleRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'bulk_mode' => $this->boolean('bulk_mode'),
            'weekdays' => collect($this->input('weekdays', []))
                ->filter(static fn ($value) => $value !== null && $value !== '')
                ->map(static fn ($value) => (int) $value)
                ->values()
                ->all(),
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'schedule_date' => ['nullable', 'date', Rule::requiredIf(fn () => ! $this->boolean('bulk_mode'))],
            'schedule_type' => ['required', Rule::in(['fixed', 'rotating', 'flexible'])],
            'time_in' => ['nullable', 'date_format:H:i'],
            'time_out' => ['nullable', 'date_format:H:i'],
            'break_start' => ['nullable', 'date_format:H:i'],
            'break_end' => ['nullable', 'date_format:H:i'],
            'is_rest_day' => ['nullable', 'boolean'],
            'bulk_mode' => ['nullable', 'boolean'],
            'date_from' => ['nullable', 'required_if:bulk_mode,1', 'date'],
            'date_to' => ['nullable', 'required_if:bulk_mode,1', 'date', 'after_or_equal:date_from'],
            'weekdays' => ['nullable', 'required_if:bulk_mode,1', 'array', 'min:1'],
            'weekdays.*' => ['integer', Rule::in([0, 1, 2, 3, 4, 5, 6])],
        ];
    }
}
