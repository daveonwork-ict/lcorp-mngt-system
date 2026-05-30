<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\EmployeeSchedule;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class StaffScheduleTestingSeeder extends Seeder
{
    public function run(): void
    {
        $days = max((int) env('RMS_TEST_SCHEDULE_DAYS', 14), 1);
        $pastDays = max((int) env('RMS_TEST_SCHEDULE_PAST_DAYS', 7), 0);
        $start = Carbon::now('Asia/Manila')->startOfDay();
        $activeBranchId = Branch::query()->where('is_active', true)->value('id');

        $staffUsers = User::query()
            ->with('role')
            ->where(function ($query): void {
                $query->where('status', 'active')->orWhere('is_active', true);
            })
            ->whereHas('role', fn ($role) => $role->where('code', 'staff_user'))
            ->get();

        foreach ($staffUsers as $user) {
            $branchId = $user->primary_branch_id ?? $activeBranchId;

            if (! $branchId) {
                continue;
            }

            for ($offset = -$pastDays; $offset < $days; $offset++) {
                $scheduleDate = $start->copy()->addDays($offset);
                $isRestDay = (int) $scheduleDate->dayOfWeek === 0;

                EmployeeSchedule::query()->updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'schedule_date' => $scheduleDate->toDateString(),
                    ],
                    [
                        'branch_id' => $branchId,
                        'schedule_type' => 'fixed',
                        'time_in' => $isRestDay ? null : '09:00',
                        'time_out' => $isRestDay ? null : '18:00',
                        'break_start' => $isRestDay ? null : '12:00',
                        'break_end' => $isRestDay ? null : '13:00',
                        'is_rest_day' => $isRestDay,
                    ]
                );
            }
        }
    }
}
