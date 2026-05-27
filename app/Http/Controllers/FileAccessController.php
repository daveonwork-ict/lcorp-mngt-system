<?php

namespace App\Http\Controllers;

use App\Models\FileAccessLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FileAccessController extends Controller
{
    public function index(Request $request): View
    {
        $logs = FileAccessLog::query()
            ->with(['user', 'branch'])
            ->when($request->filled('module_name'), fn ($q) => $q->where('module_name', $request->string('module_name')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.security.file-access-logs', [
            'logs' => $logs,
            'filters' => $request->only(['module_name', 'status']),
        ]);
    }
}
