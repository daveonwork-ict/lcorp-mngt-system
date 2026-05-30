<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\ChatRoom;
use App\Models\ChatRoomMember;
use App\Models\User;
use App\Services\ChatMessageService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;

class EmployeeDashboardCommunicationSeeder extends Seeder
{
    public function run(): void
    {
        $chatMessageService = app(ChatMessageService::class);

        $employees = User::query()
            ->with('primaryBranch')
            ->where('username', 'like', 'employee.%')
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        foreach ($employees as $employee) {
            $branchId = $employee->primary_branch_id;

            if (! $branchId) {
                continue;
            }

            $sender = $this->resolveSender($branchId, $employee->id);

            if (! $sender) {
                continue;
            }

            $room = ChatRoom::query()->firstOrCreate(
                ['room_number' => sprintf('EMP-DASH-%05d', $employee->id)],
                [
                    'room_name' => sprintf('%s Support Desk', $employee->primaryBranch?->branch_name ?? 'Employee'),
                    'room_type' => 'private',
                    'branch_id' => $branchId,
                    'created_by' => $sender->id,
                    'status' => 'active',
                ]
            );

            ChatRoomMember::query()->updateOrCreate(
                [
                    'chat_room_id' => $room->id,
                    'user_id' => $employee->id,
                ],
                [
                    'role_in_room' => 'member',
                    'joined_at' => now(),
                    'status' => 'active',
                ]
            );

            ChatRoomMember::query()->updateOrCreate(
                [
                    'chat_room_id' => $room->id,
                    'user_id' => $sender->id,
                ],
                [
                    'role_in_room' => 'admin',
                    'joined_at' => now(),
                    'status' => 'active',
                ]
            );

            if ($room->messages()->doesntExist()) {
                Auth::setUser($sender);

                $chatMessageService->send($room, $sender, [
                    'message_body' => $this->buildWelcomeMessage($employee->full_name ?: $employee->display_name, $employee->primaryBranch),
                    'message_type' => 'text',
                ]);
            }
        }

        Auth::logout();
    }

    private function resolveSender(int $branchId, int $employeeId): ?User
    {
        return User::query()
            ->where('is_active', true)
            ->where('id', '!=', $employeeId)
            ->where('username', 'not like', 'employee.%')
            ->where(function ($query) use ($branchId): void {
                $query->where('primary_branch_id', $branchId)
                    ->orWhereHas('role', fn ($role) => $role->whereIn('code', [config('rms.owner_role_code'), 'super_admin']));
            })
            ->orderByRaw('CASE WHEN primary_branch_id = ? THEN 0 ELSE 1 END', [$branchId])
            ->orderBy('id')
            ->first();
    }

    private function buildWelcomeMessage(string $employeeName, ?Branch $branch): string
    {
        $branchName = $branch?->branch_name ?? $branch?->name ?? 'your branch';

        return sprintf(
            'Hi %s, welcome to the employee self-service desk for %s. You can use this room for attendance follow-ups, payroll questions, and branch coordination.',
            $employeeName,
            $branchName
        );
    }
}
