<?php

use App\Http\Controllers\Api\DiscoveryController;
use App\Http\Controllers\Api\DraftController;
use App\Http\Controllers\Api\DraftMediaController;
use App\Http\Controllers\Api\OtpController;
use App\Http\Controllers\Customer\BillingController;
use App\Http\Controllers\Customer\CustomerOrderController;
use App\Http\Controllers\Customer\DashboardController;
use App\Http\Controllers\Customer\PageController;
use App\Http\Controllers\Customer\ProfileController;
use App\Http\Controllers\Customer\StoreSetupController;
use App\Http\Controllers\Storefront\StorefrontController;

use App\Http\Controllers\Api\PublicTemplateController;
use Illuminate\Support\Facades\Route;


/*
| Public storefronts live on their own subdomains. The host is the tenant key,
| so a store can never be reached through another store's address.
*/
Route::domain('{storeSubdomain}.'.config('platform.base_domain'))->group(function () {
    Route::get('/', [StorefrontController::class, 'show'])->name('storefront.root');
    Route::post('/checkout', [StorefrontController::class, 'order'])->middleware('throttle:12,1')->name('storefront.order');
    Route::get('/{slug}', [StorefrontController::class, 'show'])
        ->where('slug', '[a-z0-9][a-z0-9-]*')
        ->name('storefront.page');
});

/*
| Every public page is served by the same Vue application. Listing the routes
| explicitly (instead of a catch-all) keeps direct navigation and refresh
| working and reserves 404s for genuinely unknown URLs.
*/
Route::view('/', 'app')->name('home');
Route::view('/clients', 'app')->name('clients');
Route::view('/contact', 'app')->name('contact');

// Onboarding: discovery and the draft builder run in their own full-screen shell.
Route::view('/login', 'app');
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

Route::middleware('auth')->group(function () {
    Route::view('/dashboard', 'app')->name('dashboard');
    Route::view('/dashboard/{any}', 'app')->where('any', '.*')->name('dashboard.any');
});


/*
| Customer dashboard API. Every call is scoped to the signed-in owner.
*/
Route::prefix('api')->middleware('auth')->group(function () {
    Route::get('/me', [DashboardController::class, 'me']);
    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::get('/store/username', [StoreSetupController::class, 'check']);
    Route::post('/store', [StoreSetupController::class, 'store']);

    Route::get('/pages', [PageController::class, 'index']);
    Route::post('/pages', [PageController::class, 'fromDraft']);
    Route::patch('/pages/{page}', [PageController::class, 'update']);
    Route::post('/pages/{page}/edit', [PageController::class, 'edit']);
    Route::post('/pages/{page}/publish', [PageController::class, 'publish']);
    Route::post('/pages/{page}/unpublish', [PageController::class, 'unpublish']);
    Route::post('/pages/{page}/primary', [PageController::class, 'primary']);
    Route::post('/pages/{page}/duplicate', [PageController::class, 'duplicate']);
    Route::delete('/pages/{page}', [PageController::class, 'destroy']);

    Route::get('/orders', [CustomerOrderController::class, 'index']);
    Route::get('/orders/export', [CustomerOrderController::class, 'export']);
    Route::get('/orders/{order}', [CustomerOrderController::class, 'show']);
    Route::patch('/orders/{order}', [CustomerOrderController::class, 'update']);
    Route::get('/orders/{order}/invoice', [CustomerOrderController::class, 'invoice']);

    Route::get('/plans', [BillingController::class, 'plans']);
    Route::post('/plans/{plan:slug}/downgrade', [BillingController::class, 'downgrade']);
    Route::post('/plans/{plan:slug}/request', [BillingController::class, 'request']);
    Route::get('/invoices', [BillingController::class, 'invoices']);
    Route::get('/downloads', [BillingController::class, 'downloads']);
    Route::post('/auth/send-otp', [\App\Http\Controllers\Auth\PhoneLoginController::class, 'send']);
    Route::post('/auth/verify', [\App\Http\Controllers\Auth\PhoneLoginController::class, 'verify']);
    Route::post('/auth/send-otp', [\App\Http\Controllers\Auth\PhoneLoginController::class, 'send']);
    Route::post('/auth/verify', [\App\Http\Controllers\Auth\PhoneLoginController::class, 'verify']);
    Route::post('/profile/phone/send-otp', [\App\Http\Controllers\Customer\ProfileController::class, 'sendPhoneOtp']);
    Route::post('/profile/phone/verify', [\App\Http\Controllers\Customer\ProfileController::class, 'verifyPhoneOtp']);
    Route::get('/invoices/{invoice}/download', [BillingController::class, 'download']);

    Route::post('/impersonation/stop', [\App\Http\Controllers\Admin\ImpersonationController::class, 'stop']);

    Route::patch('/profile', [ProfileController::class, 'update']);
    Route::post('/profile/username', [ProfileController::class, 'username']);
});

Route::fallback(function () {
    return response()->view('app', [], 404);
});

require __DIR__.'/admin.php';
