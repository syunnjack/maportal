<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MassageSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_nationwide_and_prefectures(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('全国');
        $response->assertSee('北海道');
        $response->assertSee('沖縄県');
    }

    public function test_search_without_prefecture_redirects_to_index(): void
    {
        $response = $this->get('/search');

        $response->assertRedirect(route('massage.index'));
    }

    public function test_search_renders_places_returned_by_google(): void
    {
        Http::fake([
            'places.googleapis.com/*' => Http::response([
                'places' => [
                    [
                        'id' => 'place123',
                        'displayName' => ['text' => 'テストマッサージ東京店', 'languageCode' => 'ja'],
                        'formattedAddress' => '東京都千代田区1-1-1',
                        'rating' => 4.5,
                        'userRatingCount' => 20,
                        'googleMapsUri' => 'https://maps.google.com/?cid=123',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->get('/search?prefecture=' . urlencode('東京都'));

        $response->assertStatus(200);
        $response->assertSee('テストマッサージ東京店');
        Http::assertSentCount(3);
    }

    public function test_search_excludes_places_from_other_prefectures(): void
    {
        Http::fake([
            'places.googleapis.com/*' => Http::response([
                'places' => [
                    ['id' => '1', 'displayName' => ['text' => '東京都の店'], 'formattedAddress' => '東京都千代田区1-1-1'],
                    ['id' => '2', 'displayName' => ['text' => '埼玉県の店'], 'formattedAddress' => '埼玉県さいたま市1-1-1'],
                ],
            ], 200),
        ]);

        $response = $this->get('/search?prefecture=' . urlencode('東京都'));

        $response->assertStatus(200);
        $response->assertSee('東京都の店');
        $response->assertDontSee('埼玉県の店');
    }

    public function test_search_shows_empty_message_when_no_places_found(): void
    {
        Http::fake([
            'places.googleapis.com/*' => Http::response(['places' => []], 200),
        ]);

        $response = $this->get('/search?prefecture=' . urlencode('沖縄県'));

        $response->assertStatus(200);
        $response->assertSee('見つかりませんでした');
    }

    public function test_search_handles_api_failure_gracefully(): void
    {
        Http::fake([
            'places.googleapis.com/*' => Http::response(null, 500),
        ]);

        $response = $this->get('/search?prefecture=' . urlencode('京都府'));

        $response->assertStatus(200);
        $response->assertSee('見つかりませんでした');
    }

    public function test_tag_filter_narrows_results(): void
    {
        Http::fake([
            'places.googleapis.com/*' => Http::response([
                'places' => [
                    ['id' => '1', 'displayName' => ['text' => '女性専用サロン'], 'formattedAddress' => '大阪府大阪市1-1-1'],
                    ['id' => '2', 'displayName' => ['text' => 'ふつうの店'], 'formattedAddress' => '大阪府大阪市2-2-2'],
                ],
            ], 200),
        ]);

        $response = $this->get('/search?prefecture=' . urlencode('大阪府') . '&tag=' . urlencode('女性専用'));

        $response->assertStatus(200);
        $response->assertSee('女性専用サロン');
        $response->assertDontSee('ふつうの店');
    }

    public function test_nationwide_search_aggregates_across_prefectures(): void
    {
        Http::fake(function ($request) {
            $query = $request['textQuery'] ?? '';

            if (str_contains($query, '東京都')) {
                return Http::response([
                    'places' => [
                        ['id' => 'tokyo-1', 'displayName' => ['text' => '東京店A'], 'formattedAddress' => '東京都千代田区1-1-1'],
                    ],
                ], 200);
            }

            if (str_contains($query, '大阪府')) {
                return Http::response([
                    'places' => [
                        ['id' => 'osaka-1', 'displayName' => ['text' => '大阪店A'], 'formattedAddress' => '大阪府大阪市1-1-1'],
                    ],
                ], 200);
            }

            return Http::response(['places' => []], 200);
        });

        $response = $this->get('/search?prefecture=' . urlencode('全国'));

        $response->assertStatus(200);
        $response->assertSee('東京店A');
        $response->assertSee('大阪店A');
    }
}
