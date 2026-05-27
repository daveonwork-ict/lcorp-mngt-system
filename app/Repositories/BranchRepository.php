<?php

namespace App\Repositories;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Collection;

class BranchRepository
{
    public function allActive(): Collection
    {
        return Branch::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }
}
