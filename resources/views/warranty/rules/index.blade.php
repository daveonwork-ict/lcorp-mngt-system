@extends('layouts.app')

@section('page_title', 'Warranty Rules')
@section('content')
<div class="card mb-3"><div class="card-header">Create Rule</div><div class="card-body"><form method="POST" action="{{ route('warranty.rules.store') }}" class="form-row">@csrf
<div class="col-md-3"><input class="form-control" name="rule_name" placeholder="Rule name" required></div>
<div class="col-md-2"><select class="form-control" name="product_category_id"><option value="">Category</option>@foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->category_name }}</option>@endforeach</select></div>
<div class="col-md-2"><select class="form-control" name="brand_id"><option value="">Brand</option>@foreach($brands as $brand)<option value="{{ $brand->id }}">{{ $brand->brand_name }}</option>@endforeach</select></div>
<div class="col-md-2"><select class="form-control" name="product_id"><option value="">Product</option>@foreach($products as $product)<option value="{{ $product->id }}">{{ $product->product_name }}</option>@endforeach</select></div>
<div class="col-md-1"><input class="form-control" type="number" min="1" name="warranty_duration" placeholder="Dur" required></div>
<div class="col-md-1"><select class="form-control" name="warranty_duration_type"><option value="days">Days</option><option value="months">Months</option><option value="years">Years</option></select></div>
<div class="col-md-1"><div class="form-check mt-2"><input class="form-check-input" type="checkbox" name="requires_imei" value="1"><label class="form-check-label">IMEI</label></div></div>
<div class="col-md-2 mt-2"><select class="form-control" name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
<div class="col-md-5 mt-2"><input class="form-control" name="warranty_coverage" placeholder="Coverage details"></div>
<div class="col-md-5 mt-2"><input class="form-control" name="exclusions" placeholder="Exclusions"></div>
<div class="col-md-2 mt-2"><button class="btn btn-primary btn-block">Save</button></div>
</form></div></div>
<div class="card"><div class="card-body table-responsive p-0"><table class="table table-sm mb-0"><thead><tr><th>Code</th><th>Name</th><th>Target</th><th>Duration</th><th>Status</th></tr></thead><tbody>@foreach($rules as $rule)<tr><td>{{ $rule->rule_code }}</td><td>{{ $rule->rule_name }}</td><td>{{ $rule->product?->product_name ?? $rule->brand?->brand_name ?? $rule->category?->category_name ?? 'General' }}</td><td>{{ $rule->warranty_duration }} {{ $rule->warranty_duration_type }}</td><td>{{ ucfirst($rule->status) }}</td></tr>@endforeach</tbody></table></div><div class="card-footer">{{ $rules->links() }}</div></div>
@endsection
