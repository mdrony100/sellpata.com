<?php

use App\Http\Controllers\Api\ContactController;
use Illuminate\Support\Facades\Route;

/*
| Public JSON endpoints for the marketing site only.
| The contact form is throttled: five attempts per minute per IP.
*/
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('api.contact.store');
