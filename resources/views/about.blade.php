@extends('layouts.app')

@section('title', 'このサイトについて | マッサージ口コミポータル')
@section('description', 'マッサージ口コミポータルの運営方針、データの出典、口コミの取り扱いについて説明しています。')

@section('content')
<div class="container">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('massage.index') }}">マッサージ口コミポータル</a></li>
      <li class="breadcrumb-item active" aria-current="page">このサイトについて</li>
    </ol>
  </nav>

  <h1>このサイトについて</h1>

  <section class="mb-4">
    <h2 class="h5">サイトの目的</h2>
    <p>
      「マッサージ口コミポータル」は、全国47都道府県のマッサージ・整体・リラクゼーション店を、目的（女性専用・完全個室・指圧など）で絞り込みながら探せる検索サイトです。
      店舗の基本情報だけでなく、実際に利用した方の口コミもあわせて確認できるようにしています。
    </p>
  </section>

  <section class="mb-4">
    <h2 class="h5">掲載データの出典</h2>
    <p>
      掲載している店舗情報（店名・住所・写真・地図リンク・Googleマップ上の評価等）は、Google Places APIを通じて取得しており、随時最新の情報に更新されます。
      店舗の詳細やルート案内はGoogleマップ上で確認できます。
    </p>
  </section>

  <section class="mb-4">
    <h2 class="h5">口コミについて</h2>
    <p>
      当サイト上の口コミは、どなたでもログイン不要で投稿できます。投稿内容は運営による事前確認を行わず即時公開されますが、
      不適切な投稿を発見された場合は内容を精査のうえ対応します。口コミはあくまで投稿者個人の感想であり、
      当サイトが内容の正確性を保証するものではありません。Googleマップ上の評価とは別の、独自の口コミです。
    </p>
  </section>
</div>
@endsection
