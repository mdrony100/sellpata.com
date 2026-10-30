<?php

use Illuminate\Support\Facades\Route;

/*
| Every public page is served by the same Vue application. Listing the routes
| explicitly (instead of a catch-all) keeps direct navigation and refresh
| working and reserves 404s for genuinely unknown URLs.
*/
Route::view('/', 'app')->name('home');
Route::view('/clients', 'app')->name('clients');
Route::view('/contact', 'app')->name('contact');

Route::fallback(function () {
    return response()->view('app', [], 404);
});

require __DIR__.'/admin.php';
