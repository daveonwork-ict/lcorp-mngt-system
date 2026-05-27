<?php

namespace App\Services;

use App\Models\CashDenomination;
use App\Models\CashIn;
use App\Models\CashOpening;
use App\Models\CashOut;
use App\Models\CashVariance;
use App\Models\DailyClosing;

class DailyClosingService
{
    public function __construct(
        private readonly BranchAccessService $branchAccessService,
        private readonly CashInService $cashInService,
        private readonly CashOutService $cashOutService,
        private readonly NotificationService $notificationService,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    public function paginate(array $filters = [])
    {
        $user = auth()->user();

        return DailyClosing::query()
            ->with(['branch', 'cashier', 'reviewer', 'variance'])
            ->when(! $user || $user->role?->code !== config('rms.owner_role_code'), function ($query) use ($user): void {
                $ids = $user ? $this->branchAccessService->accessibleBranches($user)->pluck('id')->all() : [];
                $query->whereIn('branch_id', $ids ?: [-1]);
            })
            ->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['date'] ?? null, fn ($q, $date) => $q->whereDate('closing_date', $date))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
    }

    public function compute(int $branchId, int $cashierId, string $closingDate): array
    {
        $this->cashInService->syncPreparedCashIns();
        $this->cashOutService->syncAirtimeFundingCashOuts();

        $opening = (float) CashOpening::query()
            ->where('branch_id', $branchId)
            ->where('cashier_id', $cashierId)
            ->whereDate('opening_date', $closingDate)
            ->latest('id')
            ->value('opening_cash_amount');

        $productSalesCash = (float) CashIn::query()
            ->where('branch_id', $branchId)
            ->whereDate('received_at', $closingDate)
            ->where('source_type', 'product_sales')
            ->sum('amount');

        $airtimeSalesCash = (float) CashIn::query()
            ->where('branch_id', $branchId)
            ->whereDate('received_at', $closingDate)
            ->where('source_type', 'airtime_sales')
            ->sum('amount');

        $otherCashIn = (float) CashIn::query()
            ->where('branch_id', $branchId)
            ->whereDate('received_at', $closingDate)
            ->whereNotIn('source_type', ['product_sales', 'airtime_sales'])
            ->sum('amount');

        $totalCashIn = $productSalesCash + $airtimeSalesCash + $otherCashIn;

        $totalCashOut = (float) CashOut::query()
            ->where('branch_id', $branchId)
            ->whereDate('released_at', $closingDate)
            ->sum('amount');

        $expected = round($opening + $totalCashIn - $totalCashOut, 2);

        return [
            'opening_cash' => $opening,
            'product_sales_cash' => $productSalesCash,
            'airtime_sales_cash' => $airtimeSalesCash,
            'other_cash_in' => $otherCashIn,
            'total_cash_in' => $totalCashIn,
            'total_cash_out' => $totalCashOut,
            'expected_cash' => $expected,
        ];
    }

    public function upsert(array $payload): DailyClosing
    {
        $computed = $this->compute((int) $payload['branch_id'], (int) $payload['cashier_id'], $payload['closing_date']);
        $actualCash = (float) $payload['actual_cash'];
        $variance = round($actualCash - (float) $computed['expected_cash'], 2);
        $varianceType = $variance > 0 ? 'over' : ($variance < 0 ? 'short' : 'balanced');

        if ($varianceType !== 'balanced' && empty($payload['variance_explanation'])) {
            abort(422, 'Variance explanation is required when cash is over or short.');
        }

        $closing = DailyClosing::query()->updateOrCreate(
            [
                'branch_id' => $payload['branch_id'],
                'cashier_id' => $payload['cashier_id'],
                'closing_date' => $payload['closing_date'],
            ],
            [
                'closing_number' => $payload['closing_number'] ?? ('DC-'.now()->format('YmdHis').'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT)),
                'opening_cash' => $computed['opening_cash'],
                'product_sales_cash' => $computed['product_sales_cash'],
                'airtime_sales_cash' => $computed['airtime_sales_cash'],
                'other_cash_in' => $computed['other_cash_in'],
                'total_cash_in' => $computed['total_cash_in'],
                'total_cash_out' => $computed['total_cash_out'],
                'expected_cash' => $computed['expected_cash'],
                'actual_cash' => $actualCash,
                'variance_amount' => abs($variance),
                'variance_type' => $varianceType,
                'variance_explanation' => $payload['variance_explanation'] ?? null,
                'remarks' => $payload['remarks'] ?? null,
                'status' => $payload['status'] ?? 'draft',
                'submitted_by' => auth()->id(),
            ]
        );

        if (! empty($payload['denominations']) && is_array($payload['denominations'])) {
            foreach ($payload['denominations'] as $entry) {
                $denomination = (float) ($entry['denomination'] ?? 0);
                $qty = (int) ($entry['quantity'] ?? 0);
                CashDenomination::query()->updateOrCreate(
                    ['daily_closing_id' => $closing->id, 'denomination' => $denomination],
                    ['quantity' => $qty, 'total_amount' => round($denomination * $qty, 2)]
                );
            }
        }

        CashVariance::query()->updateOrCreate(
            ['daily_closing_id' => $closing->id],
            [
                'branch_id' => $closing->branch_id,
                'cashier_id' => $closing->cashier_id,
                'expected_cash' => $closing->expected_cash,
                'actual_cash' => $closing->actual_cash,
                'variance_amount' => $closing->variance_amount,
                'variance_type' => $closing->variance_type,
                'explanation' => $closing->variance_explanation,
                'resolution_status' => $closing->variance_type === 'balanced' ? 'resolved' : 'pending',
            ]
        );

        $this->auditLogService->record('finance', 'daily_closing_submitted', [], $closing->toArray(), $closing->branch_id, 'Daily closing submitted');

        if ($closing->variance_type !== 'balanced') {
            $this->notificationService->create(
                null,
                $closing->branch_id,
                $closing->variance_type === 'short' ? 'Cash shortage detected' : 'Cash overage detected',
                'Daily closing '.$closing->closing_number.' has variance.',
                'finance',
                ['daily_closing_id' => $closing->id]
            );
        }

        return $closing->fresh(['denominations', 'variance']);
    }

    public function review(DailyClosing $closing, string $status): DailyClosing
    {
        if (! in_array($status, ['reviewed', 'approved', 'rejected'], true)) {
            abort(422, 'Invalid review status.');
        }

        $before = $closing->toArray();

        $closing->update([
            'status' => $status,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $this->auditLogService->record('finance', 'daily_closing_reviewed', $before, $closing->toArray(), $closing->branch_id, 'Daily closing reviewed');

        return $closing;
    }
}
