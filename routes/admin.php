<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BenefitController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StepController;
use App\Http\Controllers\Admin\TemplateController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthController::class, 'show'])->name('login');
        Route::post('login', [AuthController::class, 'login'])->name('login.attempt');
    });

    Route::middleware('admin')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::resource('categories', CategoryController::class)->except(['show', 'create', 'edit']);
        Route::resource('templates', TemplateController::class)->except(['show', 'create', 'edit']);
        Route::resource('benefits', BenefitController::class)->except(['show', 'create', 'edit']);
        Route::resource('steps', StepController::class)->except(['show', 'create', 'edit']);
        Route::resource('clients', ClientController::class)->except(['show', 'create', 'edit']);
        Route::resource('reviews', ReviewController::class)->except(['show', 'create', 'edit']);

        Route::get('messages', [MessageController::class, 'index'])->name('messages.index');
        Route::patch('messages/{message}/handled', [MessageController::class, 'handled'])->name('messages.handled');
        Route::delete('messages/{message}', [MessageController::class, 'destroy'])->name('messages.destroy');

        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
    });
});
