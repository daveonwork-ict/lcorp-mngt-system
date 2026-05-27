@extends('layouts.app')

@section('page_title', 'Expenses')
@section('content')
<div class="card mb-3"><div class="card-header">Submit Expense</div><div class="card-body"><form method="POST" enctype="multipart/form-data" action="{{ route('expenses.store') }}" class="form-row">@csrf
<div class="col-md-2"><label>Branch</label><select class="form-control" name="branch_id" required>@foreach($branches as $branch)<option value="{{ $branch->id }}">{{ $branch->name }}</option>@endforeach</select></div>
<div class="col-md-2"><label>Category</label><select class="form-control" name="category_id" required>@foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->category_name }}</option>@endforeach</select></div>
<div class="col-md-2"><label>Date</label><input class="form-control" type="date" name="expense_date" value="{{ now()->toDateString() }}" required></div>
<div class="col-md-2"><label>Payee</label><input class="form-control" name="vendor_or_payee" required></div>
<div class="col-md-2"><label>Amount</label><input class="form-control" type="number" step="0.01" min="0.01" name="amount" required></div>
<div class="col-md-2"><label>Payment</label><select class="form-control" name="payment_method_id"><option value="">N/A</option>@foreach($paymentMethods as $method)<option value="{{ $method->id }}">{{ $method->payment_method_name }}</option>@endforeach</select></div>
<div class="col-md-4 mt-2"><label>Description</label><input class="form-control" name="description"></div>
<div class="col-md-3 mt-2"><label>Receipt</label><input class="form-control" type="file" name="receipt_file" accept=".jpg,.jpeg,.png,.pdf"></div>
<div class="col-md-3 mt-2"><label>Remarks</label><input class="form-control" name="remarks"></div>
<div class="col-md-2 mt-2"><label>&nbsp;</label><button class="btn btn-primary btn-block">Submit</button></div>
</form></div></div>

<div class="card"><div class="card-body table-responsive p-0"><table class="table table-sm mb-0"><thead><tr><th>No</th><th>Branch</th><th>Category</th><th>Payee</th><th>Amount</th><th>Status</th><th>Actions</th></tr></thead><tbody>
@foreach($expenses as $expense)
<tr>
<td><a href="{{ route('expenses.show', $expense) }}">{{ $expense->expense_number }}</a></td>
<td>{{ $expense->branch?->name }}</td><td>{{ $expense->category?->category_name }}</td><td>{{ $expense->vendor_or_payee }}</td><td>{{ number_format($expense->amount,2) }}</td>
<td><span class="badge badge-{{ $expense->status==='approved'?'success':($expense->status==='rejected'?'danger':'warning') }}">{{ ucfirst($expense->status) }}</span></td>
<td>
@if(in_array($expense->status,['pending','draft']))
<form method="POST" action="{{ route('expenses.approve',$expense) }}" class="d-inline">@csrf<button class="btn btn-xs btn-success">Approve</button></form>
<form method="POST" action="{{ route('expenses.reject',$expense) }}" class="d-inline">@csrf<input type="hidden" name="rejection_reason" value="Rejected by approver"><button class="btn btn-xs btn-danger">Reject</button></form>
@endif
</td>
</tr>
@endforeach
</tbody></table></div><div class="card-footer">{{ $expenses->links() }}</div></div>
@endsection
