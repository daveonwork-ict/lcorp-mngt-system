<?php

namespace App\Services;

use App\Models\SupportTicket;
use App\Models\User;

class SupportTicketService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly NotificationService $notificationService,
    ) {
    }

    public function paginate(User $user, array $filters = [])
    {
        $query = SupportTicket::query()->with(['reporter', 'assignee', 'branch']);

        if ($user->role?->code !== config('rms.owner_role_code')) {
            $branchIds = $user->branches()->pluck('branches.id')->all();
            $query->where(function ($scope) use ($branchIds): void {
                $scope->whereNull('branch_id')->orWhereIn('branch_id', $branchIds ?: [-1]);
            });
        }

        return $query
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['priority'] ?? null, fn ($q, $priority) => $q->where('priority', $priority))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
    }

    public function create(array $payload, User $user): SupportTicket
    {
        $ticket = SupportTicket::query()->create($payload + [
            'ticket_number' => 'TKT-'.now()->format('YmdHis').'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT),
            'reported_by' => $user->id,
            'branch_id' => $payload['branch_id'] ?? $user->primary_branch_id,
            'status' => 'open',
        ]);

        $this->auditLogService->record('deployment', 'support_ticket_created', [], $ticket->toArray(), $ticket->branch_id, 'Support ticket submitted', $user->id);

        $title = $ticket->priority === 'critical' ? 'Critical issue reported' : 'Support issue submitted';
        $this->notificationService->create(null, $ticket->branch_id, $title, 'Ticket '.$ticket->ticket_number.' submitted for '.$ticket->module_name.'.', 'deployment', ['support_ticket_id' => $ticket->id]);

        return $ticket;
    }

    public function update(SupportTicket $ticket, array $payload): SupportTicket
    {
        $before = $ticket->toArray();

        $resolvedAt = $payload['status'] === 'resolved' ? now() : null;
        $ticket->update($payload + ['resolved_at' => $resolvedAt]);

        $this->auditLogService->record('deployment', 'support_ticket_updated', $before, $ticket->fresh()->toArray(), $ticket->branch_id, 'Support ticket updated');

        if ($payload['status'] === 'resolved') {
            $this->notificationService->create($ticket->reported_by, $ticket->branch_id, 'Support issue resolved', 'Ticket '.$ticket->ticket_number.' has been resolved.', 'deployment', ['support_ticket_id' => $ticket->id]);
        }

        return $ticket->fresh();
    }
}
