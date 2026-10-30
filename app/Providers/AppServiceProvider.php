<?php

namespace App\Providers;

use App\Services\Platform\DnsLookup;
use App\Services\Platform\PhpDnsLookup;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(DnsLookup::class, PhpDnsLookup::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
