<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PlacePhotoController extends Controller
{
    /**
     * Googleの写真をアプリ経由で出す。
     *
     * 以前は <img src="...media?key=APIキー"> を直接書いていたため、
     * ページのソースを見れば誰でもAPIキーを取り出せる状態だった。
     * ここでサーバー側で実際の画像URLだけを受け取り、そこへ転送する。
     */
    public function show(string $photo): RedirectResponse
    {
        // photos/ を含む形式（places/xxx/photos/yyy）だけを受け付ける
        if (! preg_match('#^places/[A-Za-z0-9_\-]+/photos/[A-Za-z0-9_\-]+$#', $photo)) {
            throw new NotFoundHttpException;
        }

        $key = config('services.google_places.key');

        if (blank($key)) {
            throw new NotFoundHttpException;
        }

        $url = Cache::remember("place-photo:{$photo}", now()->addDays(7), function () use ($photo, $key) {
            try {
                $response = Http::timeout(10)->get("https://places.googleapis.com/v1/{$photo}/media", [
                    'maxWidthPx' => 400,
                    'skipHttpRedirect' => 'true',
                    'key' => $key,
                ]);
            } catch (ConnectionException) {
                return null;
            }

            return $response->successful() ? $response->json('photoUri') : null;
        });

        if (blank($url)) {
            throw new NotFoundHttpException;
        }

        return redirect()->away($url);
    }
}
