<?php

namespace App\Http\Requests\Hr;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAttendanceLogRequest extends FormRequest
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
            'attendance_date' => ['required', 'date'],
            'schedule_id' => ['nullable', 'integer', 'exists:employee_schedules,id'],
            'time_in' => ['required', 'date'],
            'time_out' => ['nullable', 'date', 'after_or_equal:time_in'],
            'selfie_time_in' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'selfie_time_out' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'gps_latitude_in' => ['nullable', 'numeric', 'between:-90,90'],
            'gps_longitude_in' => ['nullable', 'numeric', 'between:-180,180'],
            'gps_latitude_out' => ['nullable', 'numeric', 'between:-90,90'],
            'gps_longitude_out' => ['nullable', 'numeric', 'between:-180,180'],
            'device_info_in' => ['required', 'string', 'max:500'],
            'device_info_out' => ['nullable', 'string', 'max:500'],
            'late_minutes' => ['nullable', 'integer', 'min:0'],
            'undertime_minutes' => ['nullable', 'integer', 'min:0'],
            'overtime_minutes' => ['nullable', 'integer', 'min:0'],
            'attendance_status' => ['required', Rule::in(['present', 'late', 'absent', 'undertime', 'overtime', 'leave', 'holiday'])],
        ];
    }
}
