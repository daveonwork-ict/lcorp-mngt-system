@extends('layouts.app')

@section('page_title', 'Expense Details')
@section('content')
<div class="card mb-3"><div class="card-header">{{ $expense->expense_number }}</div><div class="card-body">
<p><strong>Branch:</strong> {{ $expense->branch?->name }}</p>
<p><strong>Category:</strong> {{ $expense->category?->category_name }}</p>
<p><strong>Payee:</strong> {{ $expense->vendor_or_payee }}</p>
<p><strong>Amount:</strong> {{ number_format($expense->amount,2) }}</p>
<p><strong>Status:</strong> {{ ucfirst($expense->status) }}</p>
<p><strong>Description:</strong> {{ $expense->description }}</p>
</div></div>
<div class="card"><div class="card-header">Attachments</div><div class="card-body">
<ul class="mb-0">
@forelse($expense->attachments as $attachment)
<li><a href="{{ route('expenses.attachments.download',$attachment) }}">{{ $attachment->file_name }}</a> ({{ number_format($attachment->file_size/1024,1) }} KB)</li>
@empty
<li>No attachments.</li>
@endforelse
</ul>
</div></div>
@endsection
