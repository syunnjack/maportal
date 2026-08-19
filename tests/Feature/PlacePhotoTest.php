<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PlacePhotoTest extends TestCase
{
    public function test_photo_url_does_not_expose_the_api_key(): void
    {
        $url = route('place-photo', ['photo' => 'places/abc123/photos/def456']);

        $this->assertStringNotContainsString('key=', $url);
        $this->assertStringContainsString('/place-photo/places/abc123/photos/def456', $url);
    }

    public function test_photo_redirects_to_the_image_returned_by_google(): void
    {
        config()->set('services.google_places.key', 'test-key');
        Http::fake([
            'places.googleapis.com/*' => Http::response(['photoUri' => 'https://example.com/photo.jpg']),
        ]);

        $this->get('/place-photo/places/abc123/photos/def456')
            ->assertRedirect('https://example.com/photo.jpg');
    }

    public function test_unexpected_photo_names_are_rejected(): void
    {
        $this->get('/place-photo/etc/passwd')->assertNotFound();
    }
}
