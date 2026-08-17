<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MassageController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SitemapController;

Route::get('/', [MassageController::class, 'index'])->name('massage.index');
Route::get('/search', [MassageController::class, 'search'])->name('massage.search');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', function () {
    $body = "User-agent: *\nDisallow:\n\nSitemap: ".route('sitemap')."\n";

    return response($body, 200, ['Content-Type' => 'text/plain']);
});
Route::view('/about', 'about')->name('about');

Route::post('/reviews', [ReviewController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('reviews.store');
