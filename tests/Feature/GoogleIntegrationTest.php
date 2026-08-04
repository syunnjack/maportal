<?php

namespace Tests\Feature;

use Tests\TestCase;

class GoogleIntegrationTest extends TestCase
{
    public function test_google_integrations_are_rendered_when_configured(): void
    {
        config([
            'services.ga4.id' => 'G-TEST123456',
            'services.google_search_console.verification' => 'verification-token',
        ]);

        $response = $this->get(route('about'));

        $response->assertOk();
        $response->assertSee('https://www.googletagmanager.com/gtag/js?id=G-TEST123456', false);
        $response->assertSee(
            '<meta name="google-site-verification" content="verification-token">',
            false
        );
    }

    public function test_google_integrations_are_omitted_when_not_configured(): void
    {
        config([
            'services.ga4.id' => null,
            'services.google_search_console.verification' => null,
        ]);

        $response = $this->get(route('about'));

        $response->assertOk();
        $response->assertDontSee('googletagmanager.com', false);
        $response->assertDontSee('google-site-verification', false);
    }
}
