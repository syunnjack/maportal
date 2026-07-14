@extends('layouts.app')

@section('title', 'マッサージ口コミポータル | 都道府県からマッサージ・リラクゼーション店を探す')
@section('description', '全国のマッサージ・整体・リラクゼーション店を都道府県から検索できるポータルサイトです。Googleマップの店舗情報に加えて、利用者のリアルな口コミも確認できます。')

@push('structured-data')
<script type="application/ld+json">
{!! json_encode([
    '@@context' => 'https://schema.org',
    '@type' => 'WebSite',
    'name' => 'マッサージ口コミポータル',
    'url' => url('/'),
    'description' => '全国のマッサージ・整体・リラクゼーション店を都道府県から検索できるポータルサイト。',
    'inLanguage' => 'ja',
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush

@section('content')
<div class="container">
  <h1>都道府県からマッサージ店を探す</h1>
  <p class="text-muted">
    マッサージ口コミポータルでは、47都道府県のマッサージ・整体・リラクゼーション店を検索できます。
    下の都道府県ボタンを選ぶと、その地域の店舗一覧（店名・住所・評価・地図リンク）が表示されます。
  </p>

  <div class="row row-cols-2 row-cols-md-4 g-2 mt-3">
    @foreach ($prefectures as $pref)
      <div class="col">
        <a href="{{ route('massage.search', ['prefecture' => $pref]) }}" class="btn btn-outline-primary w-100">
          {{ $pref }}
        </a>
      </div>
    @endforeach
  </div>

  <section class="mt-5 pt-4 border-top">
    <h2 class="h5">このサイトの特徴</h2>
    <p class="text-muted small">
      各店舗ページでは、Googleマップの評価に加えて、実際に利用した人のリアルな口コミも確認できます。
      詳しくは<a href="{{ route('about') }}">このサイトについて</a>をご覧ください。
    </p>
  </section>
</div>
@endsection
