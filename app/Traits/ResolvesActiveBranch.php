<?php

namespace App\Traits;

trait ResolvesActiveBranch
{
    public function activeBranchId(): ?int
    {
        return session('active_branch_id');
    }
}
