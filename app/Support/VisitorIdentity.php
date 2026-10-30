<?php

namespace App\Support;

use App\Models\AnonymousIdentity;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class VisitorIdentity
{
    public const COOKIE = 'sp_visitor';

    public static function hash(string $rawToken): string
    {
        return hash('sha256', $rawToken);
    }

    /** The identity for this request, without creating one. */
    public static function current(Request $request): ?AnonymousIdentity
    {
        if ($request->attributes->has('visitor_identity')) {
            return $request->attributes->get('visitor_identity');
        }

        $raw = $request->cookie(self::COOKIE);
        $identity = null;

        if (is_string($raw) && $raw !== '') {
            $identity = AnonymousIdentity::where('token_hash', self::hash($raw))
                ->where('expires_at', '>', Carbon::now())
                ->first();
        }

        $request->attributes->set('visitor_identity', $identity);

        return $identity;
    }

    /** Current identity or a freshly created one (cookie queued on the response). */
    public static function ensure(Request $request): AnonymousIdentity
    {
        if ($identity = self::current($request)) {
            return $identity;
        }

        $raw = Str::random(64);
        $days = (int) config('visitor.cookie_days', 30);

        $identity = AnonymousIdentity::create([
            'token_hash' => self::hash($raw),
            'expires_at' => Carbon::now()->addDays($days),
            'last_seen_at' => Carbon::now(),
        ]);

        Cookie::queue(cookie(
            name: self::COOKIE,
            value: $raw,
            minutes: $days * 24 * 60,
            path: '/',
            domain: null,
            secure: app()->environment('production'),
            httpOnly: true,
            raw: false,
            sameSite: 'lax',
        ));

        $request->attributes->set('visitor_identity', $identity);

        return $identity;
    }
}
