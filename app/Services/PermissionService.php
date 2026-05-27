<?php

namespace App\Services;

use App\Models\Permission;
use Illuminate\Support\Collection;

class PermissionService
{
    public function grouped(): Collection
    {
        return Permission::query()
            ->orderBy('module')
            ->orderBy('name')
            ->get()
            ->groupBy('module');
    }
}
