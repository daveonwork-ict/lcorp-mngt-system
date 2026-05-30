<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecurityHeadersFeatureTest extends TestCase
{
    public function test_login_page_includes_baseline_security_headers(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', config('security.referrer_policy'));
        $response->assertHeader('Permissions-Policy', config('security.permissions_policy'));
    }
}
