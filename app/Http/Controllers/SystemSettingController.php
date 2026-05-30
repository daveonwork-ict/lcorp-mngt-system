<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SystemSettingController extends Controller
{
    public function index(): View
    {
        $keys = [
            'branding.login_logo_path',
            'branding.sidenav_logo_path',
            'branding.favicon_path',
        ];

        $settings = SystemSetting::query()
            ->whereIn('key', $keys)
            ->pluck('value', 'key');

        return view('settings.index', [
            'settings' => $settings,
        ]);
    }

    public function updateBranding(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'login_logo' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp,svg', 'max:4096'],
            'sidenav_logo' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp,svg', 'max:4096'],
            'favicon_logo' => ['nullable', 'file', 'mimes:png,jpg,jpeg,ico,svg', 'max:2048'],
            'clear_login_logo' => ['nullable', 'boolean'],
            'clear_sidenav_logo' => ['nullable', 'boolean'],
            'clear_favicon_logo' => ['nullable', 'boolean'],
        ]);

        $this->handleAssetUpdate('branding.login_logo_path', 'login_logo', $request, (bool) ($validated['clear_login_logo'] ?? false));
        $this->handleAssetUpdate('branding.sidenav_logo_path', 'sidenav_logo', $request, (bool) ($validated['clear_sidenav_logo'] ?? false));
        $this->handleAssetUpdate('branding.favicon_path', 'favicon_logo', $request, (bool) ($validated['clear_favicon_logo'] ?? false));

        Cache::forget('branding_settings');

        return back()->with('status', 'Branding settings updated successfully.');
    }

    private function handleAssetUpdate(string $settingKey, string $inputName, Request $request, bool $clear): void
    {
        $setting = SystemSetting::query()->firstOrNew(['key' => $settingKey]);
        $setting->group = 'branding';

        if ($clear) {
            $this->deleteIfManagedUpload((string) ($setting->value ?? ''));
            $setting->value = null;
            $setting->save();

            return;
        }

        if (! $request->hasFile($inputName)) {
            if (! $setting->exists) {
                $setting->value = null;
                $setting->save();
            }

            return;
        }

        $uploaded = $request->file($inputName);
        $this->deleteIfManagedUpload((string) ($setting->value ?? ''));

        $extension = Str::lower((string) $uploaded->getClientOriginalExtension());
        $filename = Str::slug($inputName).'-'.now()->format('YmdHis').'-'.Str::random(6).'.'.$extension;
        $targetDir = public_path('uploads/branding');

        if (! File::isDirectory($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        $uploaded->move($targetDir, $filename);

        $setting->value = 'uploads/branding/'.$filename;
        $setting->save();
    }

    private function deleteIfManagedUpload(string $relativePath): void
    {
        if (! Str::startsWith($relativePath, 'uploads/branding/')) {
            return;
        }

        $absolutePath = public_path($relativePath);

        if (File::exists($absolutePath)) {
            File::delete($absolutePath);
        }
    }
}
