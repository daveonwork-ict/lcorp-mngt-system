@php
    $branches = auth()->user()?->branches()->get() ?? collect();
    $activeBranch = session('active_branch_id');
@endphp
<form action="{{ route('branch.switch') }}" method="POST" class="form-inline">
    @csrf
    <label for="branchSwitcher" class="mr-2 small text-muted">Branch</label>
    <select name="branch_id" id="branchSwitcher" class="form-control form-control-sm" onchange="this.form.submit()">
        @foreach ($branches as $branch)
            <option value="{{ $branch->id }}" @selected((int) $activeBranch === $branch->id)>{{ $branch->name }}</option>
        @endforeach
    </select>
</form>
