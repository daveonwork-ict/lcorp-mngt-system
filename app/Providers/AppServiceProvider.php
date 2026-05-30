<?php

namespace App\Providers;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFour();

        View::composer('*', function (): void {
            $defaultLogo = 'images/dits_logo.png';

            if (! Schema::hasTable('system_settings')) {
                View::share('brandingLoginLogoUrl', asset($defaultLogo));
                View::share('brandingSidenavLogoUrl', asset($defaultLogo));
                View::share('brandingFaviconUrl', asset('icons/icon-192x192.svg'));

                return;
            }

            $keys = [
                'branding.login_logo_path',
                'branding.sidenav_logo_path',
                'branding.favicon_path',
            ];

            $settings = Cache::remember('branding_settings', 600, function () use ($keys) {
                return SystemSetting::query()
                    ->whereIn('key', $keys)
                    ->pluck('value', 'key')
                    ->all();
            });

            $loginPath = (string) ($settings['branding.login_logo_path'] ?? $defaultLogo);
            $sidenavPath = (string) ($settings['branding.sidenav_logo_path'] ?? $defaultLogo);
            $faviconPath = (string) ($settings['branding.favicon_path'] ?? 'icons/icon-192x192.svg');

            $loginPath = trim($loginPath) !== '' ? ltrim($loginPath, '/') : $defaultLogo;
            $sidenavPath = trim($sidenavPath) !== '' ? ltrim($sidenavPath, '/') : $defaultLogo;
            $faviconPath = trim($faviconPath) !== '' ? ltrim($faviconPath, '/') : 'icons/icon-192x192.svg';

            $loginUrl = Str::startsWith($loginPath, ['http://', 'https://']) ? $loginPath : asset($loginPath);
            $sidenavUrl = Str::startsWith($sidenavPath, ['http://', 'https://']) ? $sidenavPath : asset($sidenavPath);
            $faviconUrl = Str::startsWith($faviconPath, ['http://', 'https://']) ? $faviconPath : asset($faviconPath);

            View::share('brandingLoginLogoUrl', $loginUrl);
            View::share('brandingSidenavLogoUrl', $sidenavUrl);
            View::share('brandingFaviconUrl', $faviconUrl);
        });
    }
}
