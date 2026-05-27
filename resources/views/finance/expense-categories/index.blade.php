@extends('layouts.app')

@section('page_title', 'Expense Categories')
@section('content')
<div class="card mb-3"><div class="card-header">Create Category</div><div class="card-body"><form method="POST" action="{{ route('expenses.categories.store') }}" class="form-row">@csrf
<div class="col-md-2"><input class="form-control" name="category_code" placeholder="Code" required></div>
<div class="col-md-3"><input class="form-control" name="category_name" placeholder="Category name" required></div>
<div class="col-md-2"><input class="form-control" type="number" step="0.01" name="monthly_budget_limit" placeholder="Budget"></div>
<div class="col-md-2"><select class="form-control" name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
<div class="col-md-1"><div class="form-check mt-2"><input class="form-check-input" type="checkbox" name="requires_approval" value="1" checked><label class="form-check-label">Approve</label></div></div>
<div class="col-md-1"><div class="form-check mt-2"><input class="form-check-input" type="checkbox" name="receipt_required" value="1"><label class="form-check-label">Receipt</label></div></div>
<div class="col-md-1"><button class="btn btn-primary btn-block">Save</button></div>
</form></div></div>

<div class="card"><div class="card-body table-responsive p-0"><table class="table table-sm mb-0"><thead><tr><th>Code</th><th>Name</th><th>Budget</th><th>Status</th></tr></thead><tbody>@foreach($categories as $category)<tr><td>{{ $category->category_code }}</td><td>{{ $category->category_name }}</td><td>{{ $category->monthly_budget_limit ? number_format($category->monthly_budget_limit,2) : '-' }}</td><td>{{ ucfirst($category->status) }}</td></tr>@endforeach</tbody></table></div><div class="card-footer">{{ $categories->links() }}</div></div>
@endsection
