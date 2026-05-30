<?php

namespace App\Services;

use App\Models\Position;
use Illuminate\Pagination\LengthAwarePaginator;

class PositionService
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return Position::query()
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where(function ($sub) use ($search): void {
                $sub->where('position_code', 'like', "%{$search}%")
                    ->orWhere('position_name', 'like', "%{$search}%")
                    ->orWhere('department', 'like', "%{$search}%");
            }))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();
    }

    public function create(array $data): Position
    {
        $position = Position::query()->create($data);

        $this->auditLogService->record('hr_positions', 'position_created', [], $position->toArray(), null, 'HR position created');

        return $position;
    }

    public function update(Position $position, array $data): Position
    {
        $before = $position->toArray();
        $position->update($data);

        $this->auditLogService->record('hr_positions', 'position_updated', $before, $position->toArray(), null, 'HR position updated');

        return $position;
    }
}
