<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Support\MassageTagger;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class MassageController extends Controller
{
    private const PREFECTURES = [
        '北海道', '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県',
        '茨城県', '栃木県', '群馬県', '埼玉県', '千葉県', '東京都', '神奈川県',
        '新潟県', '富山県', '石川県', '福井県', '山梨県', '長野県', '岐阜県',
        '静岡県', '愛知県', '三重県', '滋賀県', '京都府', '大阪府', '兵庫県',
        '奈良県', '和歌山県', '鳥取県', '島根県', '岡山県', '広島県', '山口県',
        '徳島県', '香川県', '愛媛県', '高知県', '福岡県', '佐賀県', '長崎県',
        '熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県',
    ];

    private const FIELD_MASK = 'places.id,places.displayName,places.formattedAddress,'
        . 'places.rating,places.userRatingCount,places.photos,places.googleMapsUri,'
        . 'places.nationalPhoneNumber,places.websiteUri';

    public function index()
    {
        return view('massage.index', ['prefectures' => self::PREFECTURES]);
    }

    public function search(Request $request)
    {
        $prefecture = $request->input('prefecture', '');

        if ($prefecture === '') {
            return redirect()->route('massage.index');
        }

        $results = Cache::remember("massage-search:{$prefecture}", now()->addHour(), function () use ($prefecture) {
            $apiKey = env('GOOGLE_PLACES_API_KEY');
            if (blank($apiKey)) {
                return [];
            }

            try {
                $response = Http::timeout(5)
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                        'X-Goog-Api-Key' => $apiKey,
                        'X-Goog-FieldMask' => self::FIELD_MASK,
                    ])
                    ->post('https://places.googleapis.com/v1/places:searchText', [
                        'textQuery' => $prefecture . ' マッサージ',
                        'languageCode' => 'ja',
                        'regionCode' => 'JP',
                    ]);
            } catch (ConnectionException) {
                return [];
            }

            return $response->successful() ? ($response->json('places') ?? []) : [];
        });

        // textQueryはあいまい一致のため、選択された都道府県以外の
        // 店舗も混ざる。住所に都道府県名が含まれるかで厳密に絞り込む。
        $results = array_values(array_filter($results, function ($place) use ($prefecture) {
            return str_contains($place['formattedAddress'] ?? '', $prefecture);
        }));

        $tagsByPlaceId = [];
        $availableTags = [];
        foreach ($results as $place) {
            $tags = MassageTagger::extract($place['displayName']['text'] ?? '');
            if (isset($place['id'])) {
                $tagsByPlaceId[$place['id']] = $tags;
            }
            $availableTags = array_unique(array_merge($availableTags, $tags));
        }
        sort($availableTags);

        $tag = $request->input('tag', '');
        if ($tag !== '') {
            $results = array_values(array_filter($results, function ($place) use ($tag, $tagsByPlaceId) {
                return in_array($tag, $tagsByPlaceId[$place['id'] ?? null] ?? [], true);
            }));
        }

        $placeIds = collect($results)
            ->map(fn ($place) => $place['id'] ?? null)
            ->filter()
            ->values();

        $reviews = Review::whereIn('place_id', $placeIds)
            ->latest()
            ->get()
            ->groupBy('place_id');

        $faq = $this->buildFaq($prefecture, $results, $reviews, $tagsByPlaceId);

        return view('massage.results', compact(
            'results', 'prefecture', 'reviews', 'tagsByPlaceId', 'availableTags', 'tag', 'faq'
        ));
    }

    private function buildFaq(string $prefecture, array $results, Collection $reviews, array $tagsByPlaceId): array
    {
        $womenOnlyCount = collect($tagsByPlaceId)->filter(fn ($tags) => in_array('女性専用', $tags, true))->count();

        $topRated = $reviews->filter(fn ($group) => $group->count() > 0)
            ->sortByDesc(fn ($group) => $group->avg('rating'))
            ->first();
        $topRatedName = $topRated ? $topRated->first()->shop_name : null;

        $faq = [
            [
                'question' => $prefecture . 'に女性専用のマッサージ店はありますか？',
                'answer' => $womenOnlyCount > 0
                    ? "はい、{$prefecture}には女性専用を明記している店舗が{$womenOnlyCount}件あります。一覧の「女性専用」タグで絞り込めます。"
                    : "現在の掲載データでは、{$prefecture}で女性専用を明記している店舗は見つかりませんでした。",
            ],
            [
                'question' => $prefecture . 'のマッサージ店の口コミは見られますか？',
                'answer' => '各店舗ページで、Googleマップの評価に加えて、当サイト独自に投稿された口コミ（評価と感想）も確認できます。口コミはどなたでもログイン不要で投稿できます。',
            ],
        ];

        if ($topRatedName) {
            $faq[] = [
                'question' => $prefecture . 'でおすすめのマッサージ店は？',
                'answer' => "口コミ評価をもとにすると、{$topRatedName}が現在最も高い評価を得ています。ただし好みは人それぞれのため、他の店舗の口コミもあわせてご確認ください。",
            ];
        }

        return $faq;
    }
}
