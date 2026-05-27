<?php

namespace App\Services;

class DeviceDetectionService
{
    public function detect(?string $userAgent = null): array
    {
        $ua = strtolower($userAgent ?: (string) request()->userAgent());

        $platform = 'desktop';
        if (str_contains($ua, 'android')) {
            $platform = 'android';
        } elseif (str_contains($ua, 'iphone') || str_contains($ua, 'ipad') || str_contains($ua, 'ios')) {
            $platform = 'ios';
        }

        $browser = 'unknown';
        if (str_contains($ua, 'edg/')) {
            $browser = 'edge';
        } elseif (str_contains($ua, 'chrome/')) {
            $browser = 'chrome';
        } elseif (str_contains($ua, 'safari/') && ! str_contains($ua, 'chrome/')) {
            $browser = 'safari';
        } elseif (str_contains($ua, 'firefox/')) {
            $browser = 'firefox';
        }

        $deviceType = 'desktop';
        if (str_contains($ua, 'tablet') || str_contains($ua, 'ipad')) {
            $deviceType = 'tablet';
        } elseif (str_contains($ua, 'mobile') || str_contains($ua, 'iphone') || str_contains($ua, 'android')) {
            $deviceType = 'phone';
        }

        return [
            'platform' => $platform,
            'browser' => $browser,
            'device_type' => $deviceType,
        ];
    }
}
