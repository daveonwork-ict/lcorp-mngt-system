@extends('layouts.app')

@section('page_title', 'Payslips')
@section('content')
@unless($selfService ?? false)
<div class="card mb-3">
    <div class="card-header">Generate Payslip</div>
    <div class="card-body">
        <form method="POST" action="{{ route('hr.payslips.generate', 0) }}" id="generate-payslip-form" class="form-row">@csrf
            <div class="col-md-10 mb-2">
                <select class="form-control" id="payroll_item_id" required>
                    <option value="">Select Payroll Item</option>
                    @foreach($payrollItems as $item)
                        <option value="{{ $item->id }}">#{{ $item->id }} - {{ $item->user?->display_name }} - {{ $item->run?->period?->period_code }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mb-2"><button class="btn btn-primary btn-block">Generate</button></div>
        </form>
    </div>
</div>
@endunless

<div class="card">
    <div class="card-header">Generated Payslips</div>
    <div class="table-responsive p-0">
        <table class="table table-hover mb-0">
            <thead><tr><th>Payslip #</th><th>Employee</th><th>Period</th><th>Generated At</th><th></th></tr></thead>
            <tbody>
            @forelse($payslips as $payslip)
                <tr>
                    <td>{{ $payslip->payslip_number }}</td>
                    <td>{{ $payslip->payrollItem?->user?->display_name }}</td>
                    <td>{{ $payslip->payrollItem?->run?->period?->period_code }}</td>
                    <td>{{ optional($payslip->generated_at)->format('Y-m-d H:i') }}</td>
                    <td class="text-nowrap">
                        <a href="{{ route('hr.payslips.print', ['payslip' => $payslip, 'autoprint' => 1]) }}" target="_blank" rel="noopener" class="btn btn-xs btn-outline-secondary">Print</a>
                        <a href="{{ route('hr.payslips.download', $payslip) }}" class="btn btn-xs btn-outline-primary">Download DOC</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted">No payslips generated.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $payslips->links() }}</div>
</div>
@endsection

@unless($selfService ?? false)
@push('scripts')
<script>
document.getElementById('generate-payslip-form').addEventListener('submit', function (event) {
    const payrollItemId = document.getElementById('payroll_item_id').value;

    if (!payrollItemId) {
        event.preventDefault();
        return;
    }

    this.action = '{{ route('hr.payslips.generate', ['item' => '__ID__']) }}'.replace('__ID__', payrollItemId);
});
</script>
@endpush
@endunless
