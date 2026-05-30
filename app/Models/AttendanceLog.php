<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceLog extends Model
{
    protected $fillable = [
        'user_id',
        'branch_id',
        'attendance_date',
        'schedule_id',
        'time_in',
        'time_out',
        'selfie_time_in_path',
        'selfie_time_out_path',
        'gps_latitude_in',
        'gps_longitude_in',
        'gps_latitude_out',
        'gps_longitude_out',
        'device_info_in',
        'device_info_out',
        'capture_metadata_in',
        'capture_metadata_out',
        'ip_address_in',
        'ip_address_out',
        'late_minutes',
        'undertime_minutes',
        'overtime_minutes',
        'attendance_status',
    ];

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'time_in' => 'datetime',
            'time_out' => 'datetime',
            'device_info_in' => 'array',
            'device_info_out' => 'array',
            'capture_metadata_in' => 'array',
            'capture_metadata_out' => 'array',
            'gps_latitude_in' => 'decimal:7',
            'gps_longitude_in' => 'decimal:7',
            'gps_latitude_out' => 'decimal:7',
            'gps_longitude_out' => 'decimal:7',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(EmployeeSchedule::class, 'schedule_id');
    }
}
