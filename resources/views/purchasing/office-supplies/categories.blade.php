@extends('layouts.app')

@section('page_title', 'Office Supply Categories')
@section('content')
<div class="card mb-3"><div class="card-header">New Category</div><div class="card-body"><form method="POST" action="{{ route('office-supplies.categories.store') }}" class="form-row">@csrf<div class="col-md-4 mb-2"><input class="form-control" name="category_name" placeholder="Category name" required></div><div class="col-md-5 mb-2"><input class="form-control" name="description" placeholder="Description"></div><div class="col-md-2 mb-2"><select class="form-control" name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div><div class="col-md-1 mb-2"><button class="btn btn-primary btn-block">Save</button></div></form></div></div>
<div class="card"><div class="card-header">Category List</div><div class="table-responsive p-0"><table class="table table-sm mb-0"><thead><tr><th>Code</th><th>Name</th><th>Status</th></tr></thead><tbody>@forelse($categories as $row)<tr><td>{{ $row->category_code }}</td><td>{{ $row->category_name }}</td><td>{{ ucfirst($row->status) }}</td></tr>@empty<tr><td colspan="3" class="text-center text-muted">No categories.</td></tr>@endforelse</tbody></table></div><div class="card-footer">{{ $categories->links() }}</div></div>
@endsection
