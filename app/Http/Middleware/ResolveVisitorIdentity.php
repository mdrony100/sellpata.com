<?php

namespace App\Http\Middleware;

use App\Support\VisitorIdentity;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveVisitorIdentity
{
    public function handle(Request $request, Closure $next): Response
    {
        // Resolve only; identities are created when a draft is actually started.
        VisitorIdentity::current($request);

        return $next($request);
    }
}
