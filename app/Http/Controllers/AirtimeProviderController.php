<?php

namespace App\Http\Controllers;

use App\Models\AirtimeProvider;
use App\Services\AirtimeProviderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AirtimeProviderController extends Controller
{
    public function __construct(private readonly AirtimeProviderService $providerService)
    {
    }

    public function index(): View
    {
        return view('airtime.providers.index', [
            'providers' => $this->providerService->paginate(),
            'providerModel' => new AirtimeProvider(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'provider_code' => ['required', 'string', 'max:80', 'unique:airtime_providers,provider_code'],
            'provider_name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'default_commission_type' => ['required', 'in:fixed,percentage,none'],
            'default_commission_value' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $this->providerService->create($validated);

        return back()->with('status', 'Airtime provider created.');
    }

    public function update(Request $request, AirtimeProvider $provider): RedirectResponse
    {
        $validated = $request->validate([
            'provider_code' => ['required', 'string', 'max:80', 'unique:airtime_providers,provider_code,'.$provider->id],
            'provider_name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'default_commission_type' => ['required', 'in:fixed,percentage,none'],
            'default_commission_value' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $this->providerService->update($provider, $validated);

        return back()->with('status', 'Airtime provider updated.');
    }
}
