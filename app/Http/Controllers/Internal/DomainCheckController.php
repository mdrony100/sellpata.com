<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Services\Platform\CustomDomainService;
use Illuminate\Http\Request;

/**
 * On-demand TLS asks this before issuing a certificate.
 * Only a request that both originates from and is addressed to loopback
 * may pass, so nginx forwarding a public Host still gets a refusal.
 */
class DomainCheckController extends Controller
{
    public function __invoke(Request $request, CustomDomainService $domains)
    {
        $remote = strtolower((string) $request->server('REMOTE_ADDR'));
        $host = strtolower(trim($request->getHost(), '[]'));

        $loopback = in_array($remote, ['127.0.0.1', '::1'], true)
            && in_array($host, ['127.0.0.1', 'localhost', '::1'], true);

        abort_unless($loopback, 403);

        $name = $domains->normalize((string) $request->query('domain', ''));
        $store = $name === '' ? null : $domains->verifiedStoreForHost($name);

        abort_unless($store, 404);

        return response("ok\n", 200)->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
