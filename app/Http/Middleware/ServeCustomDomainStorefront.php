<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Storefront\StorefrontController;
use App\Services\Platform\CustomDomainService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Runs at the end of the web group, after the session exists, and only
 * replaces the response when the Host is a verified custom domain.
 * Every other host continues through the route that already matched.
 */
class ServeCustomDomainStorefront
{
    public function __construct(private CustomDomainService $domains) {}

    public function handle(Request $request, Closure $next): Response
    {
        $host = strtolower($request->getHost());

        if ($host === '' || $this->domains->isLoopbackHost($host) || $this->domains->isPlatformHost($host)) {
            return $next($request);
        }

        $store = $this->domains->verifiedStoreForHost($host);

        if (! $store || ! $this->isStorefrontRequest($request)) {
            return $next($request);
        }

        $path = trim($request->path(), '/');
        $controller = app(StorefrontController::class);

        $result = $request->isMethod('POST') && $path === 'checkout'
            ? $controller->order($request, $store->username_lower, null)
            : $controller->show($request, $store->username_lower, $path === '' ? null : $path);

        return $result instanceof Response ? $result : response($result);
    }

    private function isStorefrontRequest(Request $request): bool
    {
        $path = trim($request->path(), '/');

        if ($path === '' && ($request->isMethod('GET') || $request->isMethod('HEAD'))) {
            return true;
        }

        if ($path === 'checkout' && $request->isMethod('POST')) {
            return true;
        }

        return ($request->isMethod('GET') || $request->isMethod('HEAD'))
            && preg_match('/^[a-z0-9][a-z0-9-]*$/', $path) === 1;
    }
}
