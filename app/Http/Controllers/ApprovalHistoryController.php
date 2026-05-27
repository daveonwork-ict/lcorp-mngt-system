<?php

namespace App\Http\Controllers;

use App\Models\ApprovalRequest;
use Illuminate\View\View;

class ApprovalHistoryController extends Controller
{
    public function show(ApprovalRequest $approvalRequest): View
    {
        return view('admin.approvals.history', [
            'approval' => $approvalRequest->load(['logs.performer', 'requester', 'branch']),
        ]);
    }
}
