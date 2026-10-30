<?php

use App\Http\Controllers\Api\DiscoveryController;
use App\Http\Controllers\Api\DraftController;
use App\Http\Controllers\Api\DraftMediaController;
use App\Http\Controllers\Api\OtpController;
use App\Http\Controllers\Api\PublicTemplateController;
use Illuminate\Support\Facades\Route;

/*
| Every public page is served by the same Vue application. Listing the routes
| explicitly (instead of a catch-all) keeps direct navigation and refresh
| working and reserves 404s for genuinely unknown URLs.
*/
Route::view('/', 'app')->name('home');
Route::view('/clients', 'app')->name('clients');
Route::view('/contact', 'app')->name('contact');

// Onboarding: discovery and the draft builder run in their own full-screen shell.
Route::view('/start', 'app')->name('start');
Route::view('/builder/{publicId}', 'app')->name('builder')->whereUuid('publicId');

// Template demos are real renders, clearly labelled and kept out of search engines.
Route::get('/demo/{slug}', function (string $slug) {
    return response()
        ->view('app', [], 200)
        ->header('X-Robots-Tag', 'noindex, nofollow');
})->name('demo');

/*
| Draft + OTP APIs live on the web middleware so they share the session and
| CSRF protection the SPA already sends.
*/
Route::prefix('api')->middleware('visitor')->group(function () {
    Route::get('/discovery', [DiscoveryController::class, 'index'])->name('api.discovery');
    Route::get('/demo/{slug}', [PublicTemplateController::class, 'show'])->name('api.demo');

    Route::post('/drafts', [DraftController::class, 'store'])->name('api.drafts.store');
    Route::get('/drafts/{publicId}', [DraftController::class, 'show'])->name('api.drafts.show');
    Route::patch('/drafts/{publicId}', [DraftController::class, 'update'])->name('api.drafts.update');

    Route::post('/drafts/{publicId}/media', [DraftMediaController::class, 'store'])->name('api.drafts.media.store');
    Route::post('/drafts/{publicId}/otp', [OtpController::class, 'send'])->name('api.otp.send');
    Route::post('/drafts/{publicId}/otp/verify', [OtpController::class, 'verify'])->name('api.otp.verify');
});

// Signed, short-lived image access for private draft media.
Route::get('/api/draft-media/{media}', [DraftMediaController::class, 'show'])
    ->middleware('signed')
    ->name('draft.media.show');

Route::fallback(function () {
    return response()->view('app', [], 404);
});

require __DIR__.'/admin.php';
