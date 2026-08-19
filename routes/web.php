<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MassageController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\PlacePhotoController;
use App\Http\Controllers\SitemapController;

Route::get('/', [MassageController::class, 'index'])->name('massage.index');
Route::get('/search', [MassageController::class, 'search'])->name('massage.search');
// 店舗写真。APIキーをページに出さないため、アプリ経由で転送する。
Route::get('/place-photo/{photo}', [PlacePhotoController::class, 'show'])
    ->where('photo', 'places/[A-Za-z0-9_\-]+/photos/[A-Za-z0-9_\-]+')
    ->name('place-photo');

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::view('/about', 'about')->name('about');

Route::post('/reviews', [ReviewController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('reviews.store');
