@extends('layouts.app')

@section('page_title', 'Warranty Details')
@section('content')
<div class="card mb-3"><div class="card-header">{{ $warranty->warranty_number }}</div><div class="card-body">
<p><strong>Customer:</strong> {{ $warranty->customer?->full_name }}</p>
<p><strong>Product:</strong> {{ $warranty->product?->product_name }}</p>
<p><strong>IMEI:</strong> {{ $warranty->imei?->imei_number ?? '-' }}</p>
<p><strong>Receipt:</strong> {{ $warranty->sale?->sales_number }}</p>
<p><strong>Start:</strong> {{ optional($warranty->warranty_start_date)->format('Y-m-d') }}</p>
<p><strong>End:</strong> {{ optional($warranty->warranty_end_date)->format('Y-m-d') }}</p>
<p><strong>Status:</strong> {{ ucfirst($warranty->warranty_status) }}</p>
<p><strong>Coverage:</strong> {{ $warranty->coverage_details ?? '-' }}</p>
<p><strong>Exclusions:</strong> {{ $warranty->exclusions ?? '-' }}</p>
</div></div>

<div class="card"><div class="card-header">Claim Timeline</div><div class="card-body">
@foreach($warranty->claims as $claim)
    <div class="mb-2 p-2 border rounded">
        <strong>{{ $claim->claim_number }}</strong> ({{ ucfirst(str_replace('_',' ', $claim->claim_status)) }})
        <div>{{ $claim->issue_description }}</div>
        <ul class="mb-0 mt-2">
            @foreach($claim->statusLogs as $log)
                <li>{{ $log->created_at }} - {{ ucfirst(str_replace('_',' ', $log->status)) }} {{ $log->remarks ? ('- '.$log->remarks) : '' }}</li>
            @endforeach
        </ul>
    </div>
@endforeach
</div></div>
@endsection
