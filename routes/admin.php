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

        Route::get('customers', [\App\Http\Controllers\Admin\CustomerController::class, 'index'])->name('customers.index');
        Route::get('customers/{user}', [\App\Http\Controllers\Admin\CustomerController::class, 'show'])->name('customers.show');
        Route::post('customers/{store}/suspend', [\App\Http\Controllers\Admin\CustomerController::class, 'suspend'])->name('customers.suspend');
        Route::post('customers/{store}/reactivate', [\App\Http\Controllers\Admin\CustomerController::class, 'reactivate'])->name('customers.reactivate');
        Route::post('customers/{store}/plan', [\App\Http\Controllers\Admin\CustomerController::class, 'assignPlan'])->name('customers.plan');

        Route::get('orders', [\App\Http\Controllers\Admin\PlatformOrderController::class, 'index'])->name('orders.index');

        Route::get('plans', [\App\Http\Controllers\Admin\PlanController::class, 'index'])->name('plans.index');
        Route::patch('plans/{plan}', [\App\Http\Controllers\Admin\PlanController::class, 'update'])->name('plans.update');

        Route::post('impersonate/{user}', [\App\Http\Controllers\Admin\ImpersonationController::class, 'start'])->name('impersonate.start');
        Route::post('impersonate-stop', [\App\Http\Controllers\Admin\ImpersonationController::class, 'stop'])->name('impersonate.stop');

});
