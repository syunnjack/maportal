@extends('layouts.app')

@section('title', ($tag ? $tag . '｜' : '') . $prefecture . 'のマッサージ店一覧 | マッサージ口コミポータル')
@section('description', $prefecture . 'にあるマッサージ・整体・リラクゼーション店の一覧です。住所・評価・地図リンク・実際に利用した人の口コミをまとめて確認できます。')

@push('structured-data')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'マッサージ口コミポータル', 'item' => url('/')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => $prefecture . 'のマッサージ店'],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@if (!empty($faq))
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => collect($faq)->map(fn ($qa) => [
        '@type' => 'Question',
        'name' => $qa['question'],
        'acceptedAnswer' => [
            '@type' => 'Answer',
            'text' => $qa['answer'],
        ],
    ])->all(),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endif
@if (!empty($results))
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'ItemList',
    'name' => $prefecture . 'のマッサージ店一覧',
    'itemListElement' => collect($results)->values()->map(function ($place, $i) use ($reviews) {
        $placeReviews = $reviews->get($place['id'] ?? null);

        $entry = [
            '@type' => 'LocalBusiness',
            'name' => $place['displayName']['text'] ?? '',
            'url' => $place['googleMapsUri'] ?? null,
            'address' => $place['formattedAddress'] ?? '',
        ];

        if ($placeReviews && $placeReviews->count() > 0) {
            $entry['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => round($placeReviews->avg('rating'), 1),
                'reviewCount' => $placeReviews->count(),
            ];
        }

        return [
            '@type' => 'ListItem',
            'position' => $i + 1,
            'item' => $entry,
        ];
    })->all(),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endif
@endpush

@section('content')
<div class="container">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('massage.index') }}">マッサージ口コミポータル</a></li>
      <li class="breadcrumb-item active" aria-current="page">{{ $prefecture }}{{ $tag ? '（' . $tag . '）' : '' }}</li>
    </ol>
  </nav>

  <h1>{{ $prefecture }}のマッサージ店一覧</h1>

  @if(!empty($availableTags))
    <div class="mb-3">
      <span class="small text-muted">目的で絞り込む:</span>
      @foreach($availableTags as $t)
        <a href="{{ route('massage.search', ['prefecture' => $prefecture, 'tag' => $t]) }}"
           class="btn btn-sm {{ $tag === $t ? 'btn-primary' : 'btn-outline-secondary' }} me-1 mb-1">{{ $t }}</a>
      @endforeach
      @if($tag !== '')
        <a href="{{ route('massage.search', ['prefecture' => $prefecture]) }}" class="btn btn-sm btn-link">絞り込み解除</a>
      @endif
    </div>
  @endif

  @if(empty($results))
    <p>{{ $prefecture }}のマッサージ店{{ $tag ? "（{$tag}）" : '' }}が見つかりませんでした。他の都道府県もあわせてご確認ください。</p>
    <a href="{{ route('massage.index') }}" class="btn btn-outline-primary">都道府県一覧に戻る</a>
  @else
    <p class="text-muted">{{ $prefecture }}にあるマッサージ店 {{ count($results) }}件を掲載しています。</p>
    @foreach($results as $place)
      @php
        $placeId = $place['id'] ?? null;
        $placeReviews = $reviews->get($placeId, collect());
        $placeTags = $tagsByPlaceId[$placeId] ?? [];
        $photoName = $place['photos'][0]['name'] ?? null;
      @endphp
      <article class="mb-4 pb-4 border-bottom">
        <h2 class="h5">{{ $place['displayName']['text'] ?? '' }}</h2>
        <address class="mb-2">{{ $place['formattedAddress'] ?? '' }}</address>
        @if(!empty($place['rating']))
          <p class="mb-1 small text-muted">Googleマップ評価: ★{{ $place['rating'] }}（{{ $place['userRatingCount'] ?? 0 }}件）</p>
        @endif
        @if(!empty($placeTags))
          <p class="mb-2">
            @foreach($placeTags as $t)
              <span class="badge bg-info text-dark me-1">{{ $t }}</span>
            @endforeach
          </p>
        @endif
        @if($photoName)
          <img src="https://places.googleapis.com/v1/{{ $photoName }}/media?maxWidthPx=300&key={{ config('services.google_places.key') }}"
               alt="{{ $place['displayName']['text'] ?? '' }}" width="200" loading="lazy">
        @endif
        <div class="mt-2 mb-2">
          @if(!empty($place['googleMapsUri']))
            <a href="{{ $place['googleMapsUri'] }}" class="btn btn-sm btn-primary" target="_blank" rel="noopener noreferrer">Googleマップで見る</a>
          @endif
          @if(!empty($place['websiteUri']))
            <a href="{{ $place['websiteUri'] }}" class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener noreferrer">公式サイト</a>
          @endif
        </div>

        <div class="mt-3">
          @if($placeReviews->isEmpty())
            <p class="text-muted small">まだ口コミがありません。最初の口コミを投稿してみませんか？</p>
          @else
            <p class="fw-bold small mb-2">
              口コミ {{ $placeReviews->count() }}件（平均★{{ round($placeReviews->avg('rating'), 1) }}）
            </p>
            @foreach($placeReviews as $review)
              <div class="border rounded p-2 mb-2 small">
                <div>{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}
                  <strong>{{ $review->nickname }}</strong>
                  <span class="text-muted">{{ $review->created_at->format('Y-m-d') }}</span>
                </div>
                <div>{{ $review->comment }}</div>
              </div>
            @endforeach
          @endif

          <details class="mt-2">
            <summary class="small">口コミを投稿する</summary>
            <form method="POST" action="{{ route('reviews.store') }}" class="mt-2">
              @csrf
              <input type="hidden" name="place_id" value="{{ $placeId }}">
              <input type="hidden" name="shop_name" value="{{ $place['displayName']['text'] ?? '' }}">
              <input type="hidden" name="prefecture" value="{{ $prefecture }}">
              <div style="position:absolute;left:-9999px;" aria-hidden="true">
                <label>ウェブサイト <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
              </div>
              <div class="mb-2">
                <label class="form-label small">ニックネーム（任意）</label>
                <input type="text" name="nickname" class="form-control form-control-sm" maxlength="30">
              </div>
              <div class="mb-2">
                <label class="form-label small">評価</label>
                <select name="rating" class="form-select form-select-sm" required>
                  <option value="">選択してください</option>
                  <option value="5">★★★★★</option>
                  <option value="4">★★★★☆</option>
                  <option value="3">★★★☆☆</option>
                  <option value="2">★★☆☆☆</option>
                  <option value="1">★☆☆☆☆</option>
                </select>
              </div>
              <div class="mb-2">
                <label class="form-label small">口コミ</label>
                <textarea name="comment" class="form-control form-control-sm" rows="3" minlength="5" maxlength="1000" required></textarea>
              </div>
              @if ($errors->any())
                <p class="text-danger small">{{ $errors->first() }}</p>
              @endif
              <button type="submit" class="btn btn-sm btn-outline-primary">投稿する</button>
            </form>
          </details>
        </div>
      </article>
    @endforeach

    @if(!empty($faq))
      <section class="mt-4 pt-4 border-top">
        <h2 class="h5">よくある質問</h2>
        @foreach($faq as $qa)
          <div class="mb-3">
            <p class="fw-bold mb-1">Q. {{ $qa['question'] }}</p>
            <p class="mb-0">A. {{ $qa['answer'] }}</p>
          </div>
        @endforeach
      </section>
    @endif
  @endif
</div>
@endsection
