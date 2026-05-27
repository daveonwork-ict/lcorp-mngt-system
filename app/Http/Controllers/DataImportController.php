<?php

namespace App\Http\Controllers;

use App\Models\DataImportLog;
use App\Services\DataImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DataImportController extends Controller
{
    public function __construct(private readonly DataImportService $service)
    {
    }

    public function index(Request $request): View
    {
        return view('admin.deployment.imports.index', [
            'logs' => $this->service->paginate($request->only(['module_name', 'status'])),
            'filters' => $request->only(['module_name', 'status']),
            'modules' => $this->service->supportedModules(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'module_name' => ['required', 'string', 'in:customers,suppliers,products'],
            'file' => ['required', 'file', 'mimetypes,text/csv,text/plain,application/csv,application/vnd.ms-excel'],
        ]);

        $log = $this->service->preview($validated['module_name'], $request->file('file'), $request->user()->id);

        return redirect()->route('deployment.imports.show', $log)->with('status', 'Import preview created. Review errors before confirming.');
    }

    public function show(DataImportLog $dataImportLog): View
    {
        return view('admin.deployment.imports.show', [
            'log' => $dataImportLog->load(['importer', 'errors']),
            'modules' => $this->service->supportedModules(),
        ]);
    }

    public function confirm(Request $request, DataImportLog $dataImportLog): RedirectResponse
    {
        $this->service->confirm($dataImportLog, $request->user()->id);

        return redirect()->route('deployment.imports.show', $dataImportLog)->with('status', 'Import confirmed.');
    }

    public function template(string $module): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return response()->streamDownload(function () use ($module): void {
            echo $this->service->templateContent($module);
        }, $module.'-import-template.csv');
    }
}
