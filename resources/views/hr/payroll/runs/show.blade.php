@extends('layouts.app')

@section('page_title', 'Payroll Run #'.$run->id)
@section('content')
<div class="card mb-3">
    <div class="card-body">
        <div class="row">
            <div class="col-md-3"><strong>Period:</strong> {{ $run->period?->period_code }}</div>
            <div class="col-md-3"><strong>Branch:</strong> {{ $run->branch?->branch_name ?? $run->branch?->name ?? 'All Branches' }}</div>
            <div class="col-md-2"><strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $run->status)) }}</div>
            <div class="col-md-2"><strong>Gross:</strong> {{ number_format((float) $run->total_gross_pay, 2) }}</div>
            <div class="col-md-2"><strong>Net:</strong> {{ number_format((float) $run->total_net_pay, 2) }}</div>
        </div>
        <hr>
        <form method="POST" action="{{ route('hr.payroll.runs.submit', $run) }}" class="d-inline">@csrf<button class="btn btn-sm btn-outline-primary" @disabled($run->status !== 'draft')>Submit</button></form>
        <form method="POST" action="{{ route('hr.payroll.runs.approve', $run) }}" class="d-inline">@csrf<button class="btn btn-sm btn-outline-success" @disabled(!in_array($run->status, ['pending_approval','manager_approved'], true))>Approve</button></form>
        <form method="POST" action="{{ route('hr.payroll.runs.release', $run) }}" class="d-inline">@csrf<button class="btn btn-sm btn-outline-dark" @disabled($run->status !== 'approved')>Release</button></form>
    </div>
</div>

<div class="card">
    <div class="card-header">Payroll Items</div>
    <div class="table-responsive p-0">
        <table class="table table-hover mb-0">
            <thead><tr><th>Employee</th><th>Basic</th><th>Overtime</th><th>Gross</th><th>Total Deductions</th><th>Net</th><th>Status</th></tr></thead>
            <tbody>
            @forelse($run->items as $item)
                <tr>
                    <td>{{ $item->user?->display_name }}</td>
                    <td>{{ number_format((float) $item->basic_pay, 2) }}</td>
                    <td>{{ number_format((float) $item->overtime_pay, 2) }}</td>
                    <td>{{ number_format((float) $item->gross_pay, 2) }}</td>
                    <td>{{ number_format((float) $item->total_deductions, 2) }}</td>
                    <td>{{ number_format((float) $item->net_pay, 2) }}</td>
                    <td>{{ ucfirst($item->status) }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted">No payroll items available.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
