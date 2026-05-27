@extends('layouts.app')

@section('page_title', 'Warranty Replacements')
@section('content')
<div class="card"><div class="card-body table-responsive p-0"><table class="table table-sm mb-0"><thead><tr><th>Claim</th><th>Customer</th><th>Status</th><th>Replacement</th></tr></thead><tbody>
@foreach($claims as $claim)
<tr>
<td>{{ $claim->claim_number }}</td>
<td>{{ $claim->customer?->full_name }}</td>
<td>{{ ucfirst(str_replace('_',' ', $claim->claim_status)) }}</td>
<td>
<form method="POST" action="{{ route('warranty.claims.replacement.store', $claim) }}" class="form-inline">@csrf
<select class="form-control form-control-sm mr-1" name="old_product_id" required>@foreach($products as $product)<option value="{{ $product->id }}">{{ $product->product_name }}</option>@endforeach</select>
<select class="form-control form-control-sm mr-1" name="replacement_product_id">@foreach($products as $product)<option value="{{ $product->id }}">{{ $product->product_name }}</option>@endforeach</select>
<input class="form-control form-control-sm mr-1" type="date" name="replacement_date" value="{{ now()->toDateString() }}" required>
<button class="btn btn-xs btn-primary">Record</button>
</form>
</td>
</tr>
@endforeach
</tbody></table></div><div class="card-footer">{{ $claims->links() }}</div></div>
@endsection
